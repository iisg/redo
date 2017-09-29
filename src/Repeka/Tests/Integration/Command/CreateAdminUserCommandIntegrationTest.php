<?php
namespace Repeka\Tests\Integration;

use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Tests\IntegrationTestCase;

class CreateAdminUserCommandIntegrationTest extends IntegrationTestCase {
    public function testSuccess() {
        $credentials = implode(' ', ['testadmin', 'adadmin']);
        $output = $this->executeCommand('repeka:create-admin-user ' . $credentials);
        $this->assertContains('New admin account has been created.', $output);
    }

    public function testCreatedAdminHasAllSystemRoles() {
        $this->testSuccess();
        $createdAdmin = $this->container->get(UserRepository::class)->loadUserByUsername('testadmin');
        $this->assertTrue($createdAdmin->hasRole(SystemUserRole::OPERATOR));
        $this->assertTrue($createdAdmin->hasRole(SystemUserRole::ADMIN));
    }
}
