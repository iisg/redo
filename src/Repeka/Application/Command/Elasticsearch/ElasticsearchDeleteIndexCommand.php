<?php
namespace Repeka\Application\Command\Elasticsearch;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchDeleteIndexCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:elasticsearch:delete-index')
            ->setDescription('Deletes Elasticsearch index.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $im = $this->getContainer()->get('elasticsearch.index_manager');
        $im->delete();
    }
}
