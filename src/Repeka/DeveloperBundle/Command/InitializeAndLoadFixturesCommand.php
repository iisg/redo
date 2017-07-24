<?php
namespace Repeka\DeveloperBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeAndLoadFixturesCommand extends Command {
    protected function configure() {
        $this
            ->setName('repeka:dev:initialize')
            ->setDescription('Purge database and uploads, initialize app and load fixtures.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->runCommand('repeka:dev:purge-db', ['--no-interaction' => true], $output);
        $this->runCommand('repeka:dev:clear-uploads', ['--no-interaction' => true], $output);
        $this->runCommand('repeka:initialize', ['--no-interaction' => true], $output);
        $this->runCommand('doctrine:fixtures:load', ['--no-interaction' => true, '--append' => true], $output);
    }

    private function runCommand(string $name, array $arguments, OutputInterface $output) {
        $command = $this->getApplication()->find($name);
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
