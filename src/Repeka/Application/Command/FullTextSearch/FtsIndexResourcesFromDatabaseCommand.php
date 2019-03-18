<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Elasticsearch\ESIndexManager;
use Repeka\Application\Elasticsearch\Model\ElasticSearch;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FtsIndexResourcesFromDatabaseCommand extends Command {
    use CommandBusAware;

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
            ->setName('repeka:fts:index-database')
            ->setDescription('Adds all resources from database to the FTS index.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->esIndexManager->exists()) {
            $totalCount = $this->resourceRepository->count([]);
            $step = 1000;
            $progress = new ProgressBar($output, $totalCount);
            $progress->start();
            for ($i = 0; $i < $totalCount; $i += $step) {
                $resourcesToIndex = FirewallMiddleware::bypass(
                    function () use ($i, $step) {
                        return $this->handleCommand(
                            ResourceListQuery::builder()
                                ->sortBy([['columnId' => 'id', 'direction' => 'ASC']])
                                ->setPage(($i / $step) + 1)
                                ->setResultsPerPage($step)
                                ->build()
                        );
                    }
                );
                try {
                    $this->elasticSearch->insertDocuments($resourcesToIndex);
                } catch (\Exception $e) {
                    $output->writeln("ERROR\n" . $e->getMessage());
                }
                $progress->advance($step);
            }
            $output->writeln('All resources from the database have been inserted into the FTS index');
        } else {
            $output->writeln('Index ' . $this->esIndexManager->getIndex() . 'does not exist');
        }
    }
}
