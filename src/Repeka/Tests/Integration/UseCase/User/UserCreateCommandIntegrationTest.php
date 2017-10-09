<?php
namespace Repeka\Tests\Integration\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Tests\IntegrationTestCase;

class UserCreateCommandIntegrationTest extends IntegrationTestCase {
    public function testCreatingUser() {
        $command = new UserCreateCommand('budynek', 'piotr');
        /** @var User $user */
        $user = $this->handleCommand($command);
        $this->assertNotNull($user->getId());
        $this->assertEquals('budynek', $user->getUsername());
    }

    public function testCreatingUserWithoutPassword() {
        $command = new UserCreateCommand('budynek');
        /** @var User $user */
        $user = $this->handleCommand($command);
        $this->assertNotNull($user->getId());
        $this->assertEquals('budynek', $user->getUsername());
    }
}
