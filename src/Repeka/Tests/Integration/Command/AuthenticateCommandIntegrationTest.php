<?php
namespace Repeka\Tests\Integration;

use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Tests\IntegrationTestCase;

class AuthenticateCommandIntegrationTest extends IntegrationTestCase {
    public function testAuthSuccess() {
        $credentials = implode(' ', [AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD]);
        $output = $this->executeCommand('repeka:authenticate ' . $credentials);
        $this->assertContains('Credentials valid', $output);
        $this->assertContains('(1)', $output);
    }

    public function testAuthFailure() {
        $credentials = implode(' ', [AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD . '1']);
        $output = $this->executeCommand('repeka:authenticate ' . $credentials);
        $this->assertContains('Credentials invalid', $output);
    }
}
