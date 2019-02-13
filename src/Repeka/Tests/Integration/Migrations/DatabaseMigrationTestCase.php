<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\IntegrationTestCase;

/**
 * @codingStandardsIgnoreStart
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
abstract class DatabaseMigrationTestCase extends IntegrationTestCase {
    protected function setUp() {
    }

    protected function clearDatabase() {
        $this->executeCommand('doctrine:schema:drop --force');
        $this->getEntityManager()->getConnection()->exec('DROP TABLE IF EXISTS public.migration_versions');
        $this->getEntityManager()->getConnection()->exec('DROP SEQUENCE IF EXISTS role_id_seq CASCADE');
        $this->getEntityManager()->getConnection()->exec('DROP TABLE IF EXISTS user_role');
        $this->getEntityManager()->getConnection()->exec('DROP TABLE IF EXISTS role');
    }

    private function loadDump(string $name) {
        $this->getEntityManager()->getConnection()->exec(file_get_contents(__DIR__ . '/dumps/' . $name . '.sql'));
    }

    protected function loadDumpV0_5() {
        $this->loadDump('0.5.0');
    }

    protected function loadDumpV0_6() {
        $this->loadDump('0.6.0');
    }

    protected function loadDumpV0_7() {
        $this->loadDump('0.7.0');
    }

    protected function loadDumpV0_8() {
        $this->loadDump('0.8.0');
    }

    protected function loadDumpV0_9() {
        $this->loadDump('0.9.0');
    }

    protected function loadDumpV0_10() {
        $this->loadDump('0.10.0');
    }

    protected function loadDumpV1_1() {
        $this->loadDump('1.1.0');
    }

    protected function migrate(string $toVersion = '') {
        $this->executeCommand(trim('doctrine:migrations:migrate ' . $toVersion));
        $this->resetEntityManager(MetadataRepository::class);
        if (!$toVersion) {
            $this->executeCommand('repeka:initialize --skip-backup --skip-migrations');
        }
    }
}
