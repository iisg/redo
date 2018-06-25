<?php
namespace Repeka\Tests\Application\Entity;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class UserEntityTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var UserEntity */
    private $user;

    protected function setUp() {
        $this->user = new UserEntity();
        $this->user->setUserData(new ResourceEntity($this->createResourceKindMock(), ResourceContents::empty()));
    }

    public function testHasRoleUserByDefault() {
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testAddsRolePrefixToSomeClassRoles() {
        $this->user->updateRoles(['ADMIN_SOME_CLASS']);
        $this->assertContains('ROLE_ADMIN_SOME_CLASS', $this->user->getRoles());
    }

    public function testSettingUsername() {
        $this->user->setUsername('chocapic');
        $this->assertEquals('chocapic', $this->user->getUsername());
    }

    public function testBelongsToUserGroup() {
        EntityUtils::forceSetId($this->user, 10);
        EntityUtils::forceSetId($this->user->getUserData(), 1);
        $this->user->getUserData()->updateContents(ResourceContents::fromArray([SystemMetadata::GROUP_MEMBER => [20, 30]]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([1]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([1, 10]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([1, 40]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([1, 20]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([10, 20, 40]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([20]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([20, 30]));
        $this->assertTrue($this->user->belongsToAnyOfGivenUserGroupsIds([30]));
        $this->assertFalse($this->user->belongsToAnyOfGivenUserGroupsIds([10]));
        $this->assertFalse($this->user->belongsToAnyOfGivenUserGroupsIds([40]));
        $this->assertFalse($this->user->belongsToAnyOfGivenUserGroupsIds([40, 50]));
        $this->assertFalse($this->user->belongsToAnyOfGivenUserGroupsIds([]));
    }
}
