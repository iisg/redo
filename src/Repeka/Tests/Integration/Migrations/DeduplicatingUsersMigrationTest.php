<?php
namespace Repeka\Tests\Integration\Migrations;

use Faker\Factory;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\User\UserCreateCommandAdjuster;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20180528200826
 */
class DeduplicatingUsersMigrationTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @var ResourceEntity[] */
    private $testAdmins = [];

    /** @before */
    public function prepare() {
        $this->loadDumpV7();
        foreach (['Admin', '123', 'b/123', 'B/123'] as $duplicatedUsername) {
            $randomUsername = UserCreateCommandAdjuster::normalizeUsername(Factory::create()->userName);
            $this->executeCommand("repeka:create-admin-user $randomUsername pass");
            $testAdmin = $this->findResourceByContents([SystemMetadata::USERNAME => $randomUsername]);
            $testAdmin->updateContents($testAdmin->getContents()->withReplacedValues(SystemMetadata::USERNAME, $duplicatedUsername));
            $this->getEntityManager()->persist($testAdmin);
            $this->testAdmins[$duplicatedUsername] = $testAdmin;
        }
        $this->getEntityManager()->flush();
        $this->migrate();
    }

    public function testUppercaseAdminIsDeleted() {
        $resource = $this->getEntityManager()->find(ResourceEntity::class, $this->testAdmins['Admin']->getId());
        $this->assertNull($resource);
    }

    public function testPkAccountsAreDeduplicated() {
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::USERNAME => '123'])->build();
        $users = $this->getResourceRepository()->findByQuery($query);
        $this->assertCount(1, $users);
        $user = $users[0];
        $this->assertEquals($this->testAdmins['123']->getId(), $user->getId());
        $user = $this->getEntityManager()->find(ResourceEntity::class, $user->getId());
        $this->assertEquals(['b/123'], $user->getValues(SystemMetadata::USERNAME));
    }
}
