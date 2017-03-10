<?php
namespace Repeka\DeveloperBundle\Command;

use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeAndLoadFixturesCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:dev-initialize')
            ->setDescription('Purge database, initialize app and load fixtures.');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $purger->purge();
        $this->runCommand('repeka:initialize', ['--no-interaction' => true], $output);
        $this->runCommand('doctrine:fixtures:load', ['--no-interaction' => true, '--append' => true], $output);
    }

    private function runCommand(string $name, array $arguments, OutputInterface $output) {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
