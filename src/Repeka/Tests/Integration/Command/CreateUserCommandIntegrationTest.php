<?php
namespace Repeka\Tests\Integration;

use Repeka\Tests\IntegrationTestCase;

class CreateUserCommandIntegrationTest extends IntegrationTestCase {
    public function testSuccess() {
        $credentials = implode(' ', ['testadmin', 'adadmin']);
        $output = $this->executeCommand('repeka:create-user ' . $credentials);
        $this->assertContains('New account has been created.', $output);
    }

    public function testDuplicateUsernameError() {
        $this->expectException(\RuntimeException::class);
        $credentials = implode(' ', ['testadmin', 'adadmin']);
        $this->executeCommand('repeka:create-user ' . $credentials);
        $this->executeCommand('repeka:create-user ' . $credentials);
    }

    public function testDuplicateUsernameErrorWhenDifferenceInCaseOnly() {
        $this->expectException(\RuntimeException::class);
        $this->executeCommand('repeka:create-user testadmin admin');
        $this->executeCommand('repeka:create-user testAdmin admin');
    }
}
