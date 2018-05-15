<?php
namespace Repeka\Tests\Integration\Migrations;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\User\UserQuery;
use Repeka\Tests\Integration\Traits\FixtureHelpers;

/**
 * @see Version20180523083737
 */
class InvertingUserGroupMemberMigrationsTest extends DatabaseMigrationTestCase {
    use FixtureHelpers;

    /** @before */
    public function prepare() {
        $this->loadDumpV7();
        $this->migrate();
    }

    public function testUserResourceKindGainsGroupMetadata() {
        $userResourceKind = $this->getAdminUser()->getUserData()->getKind();
        $metadata = $userResourceKind->getMetadataById(SystemMetadata::GROUP_MEMBER);
        $this->assertNotNull($metadata);
    }

    public function testUserGroupKindGainsGroupMetadata() {
        $this->expectException(\InvalidArgumentException::class);
        $userGroupKind = $this->findResourceByContents([SystemMetadata::USERNAME => 'Administratorzy'])->getKind();
        $userGroupKind->getMetadataById(SystemMetadata::GROUP_MEMBER);
    }

    public function testUserGroupDoesNotHaveGroupMemberInContents() {
        $userGroup = $this->findResourceByContents([SystemMetadata::USERNAME => 'Administratorzy']);
        $this->assertEmpty($userGroup->getValues(SystemMetadata::GROUP_MEMBER));
    }

    public function testAdminHasUserGroups() {
        $adminUserGroups = $this->getAdminUser()->getUserGroupsIds();
        $this->assertCount(2, $adminUserGroups);
    }

    public function testUserGroupMetadataHasRequiredResourceKindsSet() {
        $userResourceKind = $this->getAdminUser()->getUserData()->getKind();
        $groupMemberMetadata = $userResourceKind->getMetadataById(SystemMetadata::GROUP_MEMBER);
        $allowedRks = $groupMemberMetadata->getConstraints()['resourceKind'];
        $this->assertCount(1, $allowedRks);
        $this->assertNotContains(SystemResourceKind::USER, $allowedRks);
    }

    private function getAdminUser(): User {
        return $this->handleCommand(new UserQuery(1));
    }
}
