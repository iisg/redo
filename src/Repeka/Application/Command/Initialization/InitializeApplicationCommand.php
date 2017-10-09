<?php
namespace Repeka\Application\Command\Initialization;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeApplicationCommand extends Command {
    protected function configure() {
        $this
            ->setName('repeka:initialize')
            ->setDescription('Initializes data in database required for the app to work properly.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-languages'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-metadata'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-resource-kinds'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-resource-kinds-metadata'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-user-roles'), $output);
    }
}
