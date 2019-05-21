<?php
namespace Repeka\Application\Command\FullTextSearch;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class FtsInitializeCommand extends Command {
    protected function configure() {
        $this
            ->setName('repeka:fts:initialize')
            ->setDescription('Initialize FTS (create index and insert data from the database).');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', '2G');
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('repeka:elasticsearch:create-index --delete-if-exists'), $output);
        $this->getApplication()->run(new StringInput('repeka:fts:index-resources'), $output);
    }
}
