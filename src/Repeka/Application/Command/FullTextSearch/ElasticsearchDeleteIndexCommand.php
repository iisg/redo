<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Elasticsearch\ESIndexManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ElasticsearchDeleteIndexCommand extends Command {
    /** @var ESIndexManager */
    private $esIndexManager;

    public function __construct(ESIndexManager $esIndexManager) {
        parent::__construct();
        $this->esIndexManager = $esIndexManager;
    }

    protected function configure() {
        $this
            ->setName('repeka:elasticsearch:delete-index')
            ->setDescription('Deletes Elasticsearch index.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($this->esIndexManager->exists()) {
            $this->esIndexManager->delete();
        }
    }
}
