<?php
namespace Repeka\Application\Command\FullTextSearch;

use Repeka\Application\Cqrs\Middleware\FirewallMiddleware;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class FtsInitializeCommand extends ContainerAwareCommand {

    protected function configure() {
        $this
            ->setName('repeka:fts:initialize')
            ->setDescription('Initialize elasticsearch (create index and insert data from the database).');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        FirewallMiddleware::bypass(
            function () use ($output) {
                $this->getApplication()->run(new StringInput('repeka:elasticsearch:create-index' . ' --delete-if-exists'), $output);
                $this->getApplication()->run(new StringInput('repeka:fts:index-database'), $output);
            }
        );
    }
}
