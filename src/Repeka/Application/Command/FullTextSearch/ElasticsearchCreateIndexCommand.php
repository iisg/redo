<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Elasticsearch\ESIndexManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchCreateIndexCommand extends ContainerAwareCommand {
    /** @var ESIndexManager */
    private $esIndexManager;

    public function __construct(ESIndexManager $esIndexManager) {
        parent::__construct();
        $this->esIndexManager = $esIndexManager;
    }

    protected function configure() {
        $this
            ->setName('repeka:elasticsearch:create-index')
            ->setDescription('Creates and configures Elasticsearch index.')
            ->addOption('delete-if-exists', null, InputOption::VALUE_NONE, 'Drops the index if it exists instead of throwing an error.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $numberOfShards = $this->getContainer()->getParameter('elasticsearch.number_of_shards');
        $numberOfReplicas = $this->getContainer()->getParameter('elasticsearch.number_of_replicas');
        if ($this->esIndexManager->exists()) {
            if ($input->getOption('delete-if-exists')) {
                $output->writeln('Index already exists - deleting it first');
                $this->esIndexManager->delete();
            } else {
                throw new \Exception('Index already exists. Run with --delete-if-exists to re-create it.');
            }
        }
        $this->esIndexManager->create($numberOfShards, $numberOfReplicas);
        $output->writeln("New index has been created.");
    }
}
