<?php
namespace Repeka\Tests\Domain\UseCase\UserRole;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\UserRole\UserRoleUpdateCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleUpdateCommandHandler;

class UserRoleUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    public function testHandling() {
        $userRole = $this->createMock(UserRole::class);
        $userRole->expects($this->once())->method('update')->with(['PL' => 'nowa']);
        $userRoleRepository = $this->createMock(UserRoleRepository::class);
        $userRoleRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $command = new UserRoleUpdateCommand($userRole, ['PL' => 'nowa']);
        $handler = new UserRoleUpdateCommandHandler($userRoleRepository);
        $saved = $handler->handle($command);
        $this->assertSame($userRole, $saved);
    }
}
