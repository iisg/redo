<?php
namespace Repeka\DeveloperBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DevInitializeCommand extends Command {
    protected function configure() {
        $this
            ->setName('repeka:dev:initialize')
            ->setDescription('Purge database and uploads, initialize app and load fixtures.')
            ->addOption('drop', null, InputOption::VALUE_NONE, 'Drop and recreate database instead of purging.')
            ->addOption('no-fixtures', null, InputOption::VALUE_NONE, 'Skip loading fixtures.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        if ($input->getOption('drop')) {
            $this->runCommand('doctrine:database:drop', ['--force' => true, '--if-exists' => true], $output);
            $this->runCommand('doctrine:database:create', [], $output);
        } else {
            $this->runCommand('repeka:dev:purge-db', [], $output);
            $output->writeln('Database purged');
        }
        $this->runCommand('repeka:dev:clear-uploads', [], $output);
        $this->runCommand('repeka:initialize', ['--skip-backup' => true], $output);
        if (!$input->getOption('no-fixtures')) {
            $this->runCommand('doctrine:fixtures:load', ['--append' => true], $output);
        } else {
            $output->writeln('Skipping loading fixtures');
        }
        $output->writeln('<info>Application reinitialized!</info>');
    }

    private function runCommand(string $name, array $arguments, OutputInterface $output) {
        $command = $this->getApplication()->find($name);
        $arguments['--no-interaction'] = true;
        $input = new ArrayInput($arguments);
        return $command->run($input, $output);
    }
}
