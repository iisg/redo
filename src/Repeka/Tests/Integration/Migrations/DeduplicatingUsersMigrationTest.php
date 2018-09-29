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
        // added when introduced user roles; allows to create user with a command without messing up with custom SQLs that worked back then
        $this->getEntityManager()->getConnection()->exec('ALTER TABLE "user" ADD roles jsonb DEFAULT \'[]\' NOT NULL');
        // added when introduced metadata groups
        $this->getEntityManager()->getConnection()->exec('ALTER TABLE "metadata" ADD "groupid" VARCHAR(64) DEFAULT NULL');
        // added when calculating display strategies after changes
        $this->getEntityManager()->getConnection()->exec(
            'ALTER TABLE resource ADD display_strategies_dirty BOOLEAN DEFAULT \'true\' NOT NULL'
        );
        $this->getEntityManager()->getConnection()->exec(
            'ALTER TABLE resource ADD display_strategy_dependencies jsonb DEFAULT \'{}\' NOT NULL'
        );
        foreach (['Admin', '123456', 'b/123456', 'B/123456', 'b/0987654321', '0987654321'] as $duplicatedUsername) {
            $randomUsername = UserCreateCommandAdjuster::normalizeUsername(Factory::create()->userName);
            $this->executeCommand("repeka:create-user $randomUsername pass");
            $testAdmin = $this->findResourceByContents([SystemMetadata::USERNAME => $randomUsername]);
            $testAdmin->updateContents($testAdmin->getContents()->withReplacedValues(SystemMetadata::USERNAME, $duplicatedUsername));
            $this->getEntityManager()->persist($testAdmin);
            $this->testAdmins[$duplicatedUsername] = $testAdmin;
        }
        $this->getEntityManager()->flush();
        $this->migrate('20180528200826');
    }

    public function testUppercaseAdminIsDeleted() {
        $resource = $this->getEntityManager()->find(ResourceEntity::class, $this->testAdmins['Admin']->getId());
        $this->assertNull($resource);
    }

    public function testPkAccountsAreDeduplicated() {
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::USERNAME => '123456'])->build();
        $users = $this->getResourceRepository()->findByQuery($query);
        $this->assertCount(1, $users);
        $user = $users[0];
        $this->assertEquals($this->testAdmins['123456']->getId(), $user->getId());
        $user = $this->getEntityManager()->find(ResourceEntity::class, $user->getId());
        $this->assertEquals(['b/123456'], $user->getContents()->getValuesWithoutSubmetadata(SystemMetadata::USERNAME));
    }

    public function testPkAccountsWithLength10AreUntouched() {
        $query = ResourceListQuery::builder()->filterByContents([SystemMetadata::USERNAME => '0987654321'])->build();
        $users = $this->getResourceRepository()->findByQuery($query);
        $this->assertCount(2, $users);
    }
}
