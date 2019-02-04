<?php
namespace Repeka\Tests\Integration;

use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class AuthenticateCommandIntegrationTest extends IntegrationTestCase {
    protected function initializeDatabaseForTests() {
        self::loadFixture(new AdminAccountFixture());
    }

    public function testAuthSuccess() {
        $credentials = implode(' ', [AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD]);
        $output = $this->executeCommand('repeka:authenticate ' . $credentials);
        $this->assertContains('Credentials valid', $output);
        $this->assertContains(strval(AdminAccountFixture::ADMIN_USER_ID), $output);
    }

    public function testAuthFailure() {
        $credentials = implode(' ', [AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD . '1']);
        $output = $this->executeCommand('repeka:authenticate ' . $credentials);
        $this->assertContains('Credentials invalid', $output);
    }
}
