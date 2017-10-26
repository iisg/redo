<?php
namespace Repeka\Application\Command;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;

class BackupDbCommand extends ContainerAwareCommand {
    const BACKUP_DIR = \AppKernel::VAR_PATH . '/backups';

    protected function configure() {
        $this
            ->setName('db:backup')
            ->setDescription('Saves database backup to the backups directory.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var Connection $connection */
        $connection = $this->getContainer()->get(EntityManagerInterface::class)->getConnection();
        if ($connection->getDatabasePlatform()->getName() != 'postgresql') {
            $output->writeln('<warning>Only PostgreSQL database backups are supported.</warning>');
            $this->askIfContinue($input, $output);
        } else {
            $this->backupPostgresDatabase($connection, $input, $output);
        }
    }

    private function backupPostgresDatabase(Connection $connection, InputInterface $input, OutputInterface $output) {
        $backupName = 'repeka-before-' . $this->getContainer()->getParameter('application_version') . '-' . date('YmdHis') . '.sql.gz';
        $backupPath = self::BACKUP_DIR . '/' . $backupName;
        $process = new Process(sprintf(
            'pg_dump --username="%s" --host="%s" %s | gzip > "%s"',
            $connection->getUsername(),
            $connection->getHost(),
            $connection->getDatabase(),
            $backupPath
        ));
        $process->run();
        $errorOutput = trim($process->getErrorOutput());
        if ($process->isSuccessful() && !$errorOutput) {
            $output->writeln('<info>Database backup has been saved to ' . $backupName . '.</info>');
        } else {
            @unlink($backupPath);
            $output->writeln($errorOutput);
            $output->writeln('<error>Could not make the database backup.</error>');
            $this->askIfContinue($input, $output);
        }
    }

    private function askIfContinue(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $want = $helper->ask($input, $output, new ConfirmationQuestion('Do you want to continue without backup? [y/N] ', false));
        if (!$want) {
            throw new \RuntimeException('Could not create database backup.');
        }
    }
}
