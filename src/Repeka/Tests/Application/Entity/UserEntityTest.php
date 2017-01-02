<?php
namespace Repeka\Tests\Application\Entity;

use Repeka\Application\Entity\UserEntity;

class UserEntityTest extends \PHPUnit_Framework_TestCase {
    public function testHasRoleUserByDefault() {
        $user = new UserEntity();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testUpdatingStaticPermissions() {
        $user = new UserEntity();
        $user->updateStaticPermissions(['RESOURCES', 'METADATA']);
        $this->assertContains('RESOURCES', $user->getStaticPermissions());
        $this->assertContains('METADATA', $user->getStaticPermissions());
    }

    public function testMappingStaticPermissionsToSymfonyRoles() {
        $user = new UserEntity();
        $user->updateStaticPermissions(['RESOURCES']);
        $this->assertContains('ROLE_STATIC_RESOURCES', $user->getRoles());
        $this->assertNotContains('RESOURCES', $user->getRoles());
    }

    public function testUpdatingRoles() {
        $user = new UserEntity();
        $user->updateStaticPermissions(['RESOURCES']);
        $this->assertContains('ROLE_STATIC_RESOURCES', $user->getRoles());
        $user->updateStaticPermissions(['METADATA']);
        $this->assertContains('ROLE_STATIC_METADATA', $user->getRoles());
        $this->assertNotContains('ROLE_STATIC_RESOURCES', $user->getRoles());
    }
}
