<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Elasticsearch\ESIndexManager;
use Repeka\Application\Elasticsearch\Model\ElasticSearch;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FtsIndexResourcesFromDatabaseCommand extends Command {
    /** @var ElasticSearch */
    private $elasticSearch;
    /** @var ESIndexManager */
    private $esIndexManager;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ElasticSearch $elasticSearch, ESIndexManager $esIndexManager, ResourceRepository $resourceRepository) {
        parent::__construct();
        $this->elasticSearch = $elasticSearch;
        $this->esIndexManager = $esIndexManager;
        $this->resourceRepository = $resourceRepository;
    }

    protected function configure() {
        $this
            ->setName('repeka:fts:index-resources')
            ->setDescription('Indexes resources from database in the FTS index.')
            ->addOption('resourceIds', null, InputOption::VALUE_REQUIRED)
            ->addOption('offset', null, InputOption::VALUE_REQUIRED)
            ->addOption('limit', null, InputOption::VALUE_REQUIRED);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->esIndexManager->exists()) {
            ini_set('memory_limit', '2G');
            $resourceIds = array_values(array_filter(array_map('trim', explode(',', $input->getOption('resourceIds') ?? ''))));
            $offset = $input->getOption('offset') ?? 0;
            $limit = ($input->getOption('limit') ?? 1000000) + $offset;
            $totalCount = $this->resourceRepository->count([]);
            $step = 500;
            $progress = new ProgressBar($output, $totalCount);
            $progress->start();
            $progress->setProgress($offset);
            for ($i = $offset; $i < $totalCount && $i < $limit; $i += $step) {
                /** @var ResourceListQueryBuilder $query */
                $query = ResourceListQuery::builder()
                    ->sortBy([['columnId' => 'id', 'direction' => 'ASC']])
                    ->setPage(($i / $step) + 1)
                    ->setResultsPerPage($step);
                if ($resourceIds) {
                    $query->filterByIds($resourceIds);
                }
                $resourcesToIndex = $this->resourceRepository->findByQuery($query->build());
                try {
                    $this->elasticSearch->insertDocuments($resourcesToIndex);
                } catch (\Exception $e) {
                    $output->writeln("ERROR\n" . $e->getMessage());
                }
                $progress->advance($step);
            }
            $output->writeln(PHP_EOL . 'Resources from the database have been indexed.');
        } else {
            $output->writeln('Index ' . $this->esIndexManager->getIndex() . 'does not exist');
        }
    }
}
