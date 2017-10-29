<?php
namespace Repeka\Application\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use Elastica\Exception\RuntimeException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeApplicationCommand extends ContainerAwareCommand {
    protected function configure() {
        $this
            ->setName('repeka:initialize')
            ->setDescription('Initializes data in database required for the app to work properly.')
            ->addOption('skip-backup');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->setCatchExceptions(false);
        $this->waitForDatabaseConnection($output);
        $this->getApplication()->run(new StringInput('doctrine:database:create --if-not-exists --no-interaction'), $output);
        if (!$input->getOption('skip-backup')) {
            $this->getApplication()->run(new StringInput('db:backup'), $output);
        }
        $this->getApplication()->run(new StringInput('doctrine:migrations:migrate --no-interaction --allow-no-migration'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-languages'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-metadata'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-resource-kinds'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-resource-kinds-metadata'), $output);
        $this->getApplication()->run(new StringInput('repeka:initialize:system-user-roles'), $output);
    }

    private function waitForDatabaseConnection(OutputInterface $output): void {
        $dbConnectionRetries = 5;
        while (!$this->hasDatabaseConnection()) {
            if ($dbConnectionRetries <= 0) {
                throw new RuntimeException('Could not connecto to the database.');
            } else {
                $output->writeln("Waiting for database connection ($dbConnectionRetries)...");
                --$dbConnectionRetries;
                sleep(1);
            }
        }
    }

    private function hasDatabaseConnection(): bool {
        try {
            $this->getContainer()->get(EntityManagerInterface::class)->getConnection()->connect();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
