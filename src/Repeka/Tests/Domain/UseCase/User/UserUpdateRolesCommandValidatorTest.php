<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommandValidator;

class UserUpdateRolesCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    private $sampleRole;
    private $user;
    /** @var UserUpdateRolesCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new UserUpdateRolesCommandValidator();
        $this->sampleRole = $this->createMock(UserRole::class);
        $this->user = $this->createMock(User::class);
        $this->user->expects($this->atLeastOnce())->method('getId')->willReturn(1);
    }

    public function testValidCommand() {
        $command = new UserUpdateRolesCommand($this->user, [$this->sampleRole]);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testValidCommandIfNoRoles() {
        $command = new UserUpdateRolesCommand($this->user, []);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfNoUserRoleInArray() {
        $command = new UserUpdateRolesCommand($this->user, ['A']);
        $this->assertFalse($this->validator->isValid($command));
    }
}
