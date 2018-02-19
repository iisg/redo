<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Constants\SystemResourceKind;

class Version20180214232445SubmetadataValuesMigrationTest extends DatabaseMigrationTestCase {
    /** @before */
    public function prepare() {
        $this->loadDumpV5();
        $this->migrate('20180214232445');
    }

    public function testAdminAccountBeforeMigration() {
        $this->clearDatabase();
        $this->loadDumpV5();
        $adminRow = $this->getEntityManager()->getConnection()->executeQuery('SELECT * FROM resource WHERE id = 1')->fetch();
        $this->assertEquals(SystemResourceKind::USER, $adminRow['kind_id']);
        $this->assertEquals(SystemResourceClass::USER, $adminRow['resource_class']);
        $this->assertEquals(['admin'], json_decode($adminRow['contents'], true)[SystemMetadata::USERNAME]);
    }

    public function testMigratedAdminAccount() {
        $adminRow = $this->getEntityManager()->getConnection()->executeQuery('SELECT * FROM resource WHERE id = 1')->fetch();
        $this->assertEquals(SystemResourceKind::USER, $adminRow['kind_id']);
        $this->assertEquals(SystemResourceClass::USER, $adminRow['resource_class']);
        $this->assertEquals([['value' => 'admin']], json_decode($adminRow['contents'], true)[SystemMetadata::USERNAME]);
    }
}
