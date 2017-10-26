<?php
namespace Repeka\Tests\Domain\UseCase\User;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommandHandler;

class UserUpdateRolesCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var UserUpdateRolesCommandHandler */
    private $handler;
    /** @var PHPUnit_Framework_MockObject_MockObject|User */
    private $user;
    /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository */
    private $userRepository;
    /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository */
    private $userRoleRepository;

    protected function setUp() {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->userRoleRepository->expects($this->any())->method('findSystemRoles')
            ->willReturn(array_map(function (SystemUserRole $role) {
                return $role->toUserRole();
            }, SystemUserRole::values()));
        $this->userRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->handler = new UserUpdateRolesCommandHandler($this->userRepository, $this->userRoleRepository);
        $this->user = $this->createMock(User::class);
    }

    public function testHandling() {
        $role = $this->createMock(UserRole::class);
        $command = new UserUpdateRolesCommand($this->user, [$role], $this->user);
        $this->user->expects($this->once())->method('updateRoles')->with([$role]);
        $this->assertSame($this->user, $this->handler->handle($command));
    }

    public function testAddingImpliedRoles() {
        $adminRole = SystemUserRole::ADMIN()->toUserRole();
        $operatorRole = SystemUserRole::OPERATOR()->toUserRole();
        $command = new UserUpdateRolesCommand($this->user, [$adminRole], $this->user);
        $this->user->expects($this->once())->method('updateRoles')->with([$adminRole, $operatorRole]);
        $this->assertSame($this->user, $this->handler->handle($command));
    }
}
