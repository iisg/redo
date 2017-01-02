<?php
namespace Repeka\Tests\Domain\UseCase\User;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserQuery;
use Repeka\Domain\UseCase\User\UserQueryHandler;
use Repeka\Domain\UseCase\User\UserUpdateStaticPermissionsCommand;
use Repeka\Domain\UseCase\User\UserUpdateStaticPermissionsCommandHandler;

class UserUpdateStaticPermissionsCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository */
    private $userRepository;

    /** @var UserUpdateStaticPermissionsCommandHandler */
    private $handler;

    protected function setUp() {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->handler = new UserUpdateStaticPermissionsCommandHandler($this->userRepository);
    }

    public function testHandling() {
        $command = new UserUpdateStaticPermissionsCommand(1, ['A']);
        $user = $this->createMock(User::class);
        $this->userRepository->expects($this->once())->method('findOne')->willReturn($user);
        $this->userRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $user->expects($this->once())->method('updateStaticPermissions')->with(['A']);
        $this->assertSame($user, $this->handler->handle($command));
    }
}
