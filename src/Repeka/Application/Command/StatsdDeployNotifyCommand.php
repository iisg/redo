<?php
namespace Repeka\Application\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class StatsdDeployNotifyCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:statsd:deploy')
            ->setDescription('Notifies the configured StatsD instance about the new deploy.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getContainer()->get('m6_statsd')->increment('repeka.deploy')->send();
    }
}
