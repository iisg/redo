<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommandValidator;
use Repeka\Tests\Traits\StubsTrait;

class UserUpdateRolesCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private $sampleRole;
    private $user;
    private $executor;
    /** @var UserUpdateRolesCommandValidator */
    private $validator;

    protected function setUp() {
        $this->validator = new UserUpdateRolesCommandValidator();
        $this->sampleRole = $this->createMock(UserRole::class);
        $this->user = $this->createMockEntity(User::class, 1);
        $this->executor = $this->createMockEntity(User::class, 2);
    }

    public function testValidCommand() {
        $command = new UserUpdateRolesCommand($this->user, [$this->sampleRole], $this->executor);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testValidCommandIfNoRoles() {
        $command = new UserUpdateRolesCommand($this->user, [], $this->executor);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testInvalidIfNoUserRoleInArray() {
        $command = new UserUpdateRolesCommand($this->user, ['A'], $this->executor);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testUpdatingOwnRoles() {
        $command = new UserUpdateRolesCommand($this->user, [$this->sampleRole], $this->user);
        $this->assertTrue($this->validator->isValid($command));
    }

    public function testCannotRevokeSystemRolesFromMyself() {
        $systemRole = SystemUserRole::ADMIN()->toUserRole();
        $this->user->method('getUserRoles')->willReturn([$this->sampleRole, $systemRole]);
        $command = new UserUpdateRolesCommand($this->user, [$this->sampleRole], $this->user);
        $this->assertFalse($this->validator->isValid($command));
    }

    public function testGivesReasonOfWhyRevokingIsImpossible() {
        $this->expectExceptionMessage('You cannot revoke any of the system roles from yourself');
        $systemRole = SystemUserRole::ADMIN()->toUserRole();
        $this->user->method('getUserRoles')->willReturn([$this->sampleRole, $systemRole]);
        $command = new UserUpdateRolesCommand($this->user, [$this->sampleRole], $this->user);
        $this->validator->validate($command);
    }

    public function testCanRevokeNonSystemRolesFromMyself() {
        $systemRole = SystemUserRole::ADMIN()->toUserRole();
        $this->user->method('getUserRoles')->willReturn([$this->sampleRole, $systemRole]);
        $command = new UserUpdateRolesCommand($this->user, [$systemRole], $this->user);
        $this->assertTrue($this->validator->isValid($command));
    }
}
