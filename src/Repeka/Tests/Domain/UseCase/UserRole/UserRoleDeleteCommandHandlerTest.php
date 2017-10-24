<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\UserRole\UserRoleDeleteCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleDeleteCommandHandler;

class UserRoleDeleteCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var UserRoleDeleteCommandHandler */
    private $handler;
    /** @var UserRoleRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userRoleRepository;

    protected function setUp() {
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->handler = new UserRoleDeleteCommandHandler($this->userRoleRepository);
    }

    public function testDeleting() {
        $resource = $this->createMock(UserRole::class);
        $command = new UserRoleDeleteCommand($resource);
        $this->userRoleRepository->expects($this->once())->method('delete')->with($resource);
        $this->handler->handle($command);
    }
}
