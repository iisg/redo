<?php
namespace Repeka\Tests\Integration;

use Repeka\Tests\IntegrationTestCase;

/** @small */
class CreateUserCommandIntegrationTest extends IntegrationTestCase {
    public function testSuccess() {
        $output = $this->executeCommand('repeka:create-user testadmin adadmin');
        $this->assertContains('New account has been created.', $output);
    }

    /** @depends testSuccess */
    public function testDuplicateUsernameError() {
        $this->expectException(\RuntimeException::class);
        $this->executeCommand('repeka:create-user testadmin adadmin');
    }

    /** @depends testSuccess */
    public function testDuplicateUsernameErrorWhenDifferenceInCaseOnly() {
        $this->expectException(\RuntimeException::class);
        $this->executeCommand('repeka:create-user testAdmin admin');
    }
}
