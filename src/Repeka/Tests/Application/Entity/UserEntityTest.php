<?php
namespace Repeka\Tests\Application\Entity;

use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
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
}
