<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20180528200826
 */
class DeduplicatingUsersMigrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $testAdmin;

    /** @before */
    public function prepare() {
        $this->loadDumpV7();
        $this->executeCommand('repeka:create-admin-user testadmin admin');
        $this->testAdmin = $this->findResourceByContents([SystemMetadata::USERNAME => 'testadmin']);
        $this->testAdmin->updateContents($this->testAdmin->getContents()->withReplacedValues(SystemMetadata::USERNAME, 'Admin'));
        $this->getEntityManager()->persist($this->testAdmin);
        $this->getEntityManager()->flush();
        $this->migrate();
    }

    public function testUppercaseAdminIsDeleted() {
        $resource = $this->getEntityManager()->find(ResourceEntity::class, $this->testAdmin->getId());
        $this->assertNull($resource);
    }
}
