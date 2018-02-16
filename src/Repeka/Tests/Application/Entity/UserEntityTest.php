<?php
namespace Repeka\Tests\Application\Entity;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\UserRole;
use Repeka\Tests\Traits\StubsTrait;

class UserEntityTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var UserEntity */
    private $user;

    /** @var UserRole */
    private $sampleRole;

    protected function setUp() {
        $this->user = new UserEntity();
        $this->user->setUserData(new ResourceEntity($this->createResourceKindMock(), ResourceContents::empty()));
        $this->sampleRole = $this->createMock(UserRole::class);
        $this->sampleRole->method('getId')->willReturn(1);
    }

    public function testAddingRole() {
        $this->assertFalse($this->user->hasRole($this->sampleRole));
        $this->user->updateRoles([$this->sampleRole]);
        $this->assertTrue($this->user->hasRole($this->sampleRole));
    }

    public function testRemoveRole() {
        $this->user->updateRoles([$this->sampleRole]);
        $this->user->updateRoles([]);
        $this->assertFalse($this->user->hasRole($this->sampleRole));
    }

    public function testHasRoleUserByDefault() {
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testCustomRoleDoesNotAffectSecurityRoles() {
        $this->user->updateRoles([$this->sampleRole]);
        $this->assertCount(1, $this->user->getRoles());
    }

    public function testSystemRoleIsAddedToSecurityRoles() {
        $this->user->updateRoles([SystemUserRole::OPERATOR()->toUserRole()]);
        $this->assertContains('ROLE_OPERATOR', $this->user->getRoles());
        $this->assertCount(2, $this->user->getRoles());
    }

    public function testRevokingSystemRole() {
        $this->user->updateRoles([SystemUserRole::ADMIN()->toUserRole()]);
        $this->assertContains('ROLE_ADMIN', $this->user->getRoles());
        $this->user->updateRoles([]);
        $this->assertNotContains('ROLE_ADMIN', $this->user->getRoles());
    }

    public function testSettingUsername() {
        $this->user->setUsername('chocapic');
        $this->assertEquals('chocapic', $this->user->getUsername());
    }
}
