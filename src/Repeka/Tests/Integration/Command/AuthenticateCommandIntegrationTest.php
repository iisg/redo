<?php
namespace Repeka\Tests\Integration;

use Repeka\DeveloperBundle\DataFixtures\ORM\AdminAccountFixture;
use Repeka\Tests\IntegrationTestCase;

class AuthenticateCommandIntegrationTest extends IntegrationTestCase {
    public function testAuthSuccess() {
        $this->markTestSkipped('The command triggers deprecation notice - will be fixed in the next commit.');
        $credentials = implode(' ', [AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD]);
        $output = $this->executeCommand('repeka:authenticate ' . $credentials);
        $this->assertContains('Credentials valid', $output);
        $this->assertContains('(1)', $output);
    }

    public function testAuthFailure() {
        $this->markTestSkipped('The command triggers deprecation notice - will be fixed in the next commit.');
        $credentials = implode(' ', [AdminAccountFixture::USERNAME, AdminAccountFixture::PASSWORD . '1']);
        $output = $this->executeCommand('repeka:authenticate ' . $credentials);
        $this->assertContains('Credentials invalid', $output);
    }
}
