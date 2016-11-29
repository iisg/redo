<?php
namespace Repeka\Application\Command\Elasticsearch;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchCreateIndexCommand extends ContainerAwareCommand {
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
        $im = $this->getContainer()->get('elasticsearch.index_manager');
        if ($im->exists()) {
            if ($input->getOption('delete-if-exists')) {
                $output->writeln('Index already exists - deleting it first');
                $im->delete();
            } else {
                throw new \Exception('Index already exists. Run with --delete-if-exists to re-create it.');
            }
        }
        $im->create($numberOfShards, $numberOfReplicas);
    }
}
