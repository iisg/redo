<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Cqrs\CommandBusAware;
use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Repeka\Application\Elasticsearch\ESIndexManager;
use Repeka\Application\Elasticsearch\Model\ElasticSearch;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FtsIndexResourcesFromDatabaseCommand extends Command {
    use CommandBusAware;

    /** @var ElasticSearch */
    private $elasticSearch;

    /** @var ESIndexManager */
    private $esIndexManager;

    public function __construct(ElasticSearch $elasticSearch, ESIndexManager $esIndexManager) {
        parent::__construct();
        $this->elasticSearch = $elasticSearch;
        $this->esIndexManager = $esIndexManager;
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
            $resourcesToIndex = FirewallMiddleware::bypass(
                function () {
                    return $this->handleCommand(ResourceListQuery::builder()->build());
                }
            );
            $this->elasticSearch->insertDocuments($resourcesToIndex);
            $output->writeln('All resources from the database have been inserted into the FTS index');
        } else {
            $output->writeln('Index ' . $this->esIndexManager->getIndex() . 'does not exist');
        }
    }
}
