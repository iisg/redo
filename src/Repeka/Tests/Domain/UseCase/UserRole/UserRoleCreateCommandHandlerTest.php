<?php
namespace Repeka\Tests\Domain\UseCase\UserRole;

use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleCreateCommandHandler;

class UserRoleCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var UserRoleCreateCommand */
    private $command;

    /** @var \PHPUnit_Framework_MockObject_MockObject|UserRoleRepository */
    private $userRoleRepository;

    /** @var UserRoleCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->command = new UserRoleCreateCommand(['EN' => 'New workflow']);
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->userRoleRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->handler = new UserRoleCreateCommandHandler($this->userRoleRepository);
    }

    public function testCreatingUserRole() {
        $userRole = $this->handler->handle($this->command);
        $this->assertNotNull($userRole);
        $this->assertSame($this->command->getName(), $userRole->getName());
        $this->assertFalse($userRole->isSystemRole());
    }
}
