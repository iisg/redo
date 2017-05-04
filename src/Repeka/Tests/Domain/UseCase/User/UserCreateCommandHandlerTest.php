<?php
namespace Repeka\Tests\Domain\UseCase\User;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\UserFactory;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserCreateCommandHandler;

class UserCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var UserCreateCommandHandler */
    private $handler;
    /** @var UserFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $userFactory;

    protected function setUp() {
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->userFactory = $this->createMock(UserFactory::class);
        $this->handler = new UserCreateCommandHandler($userRepository, $this->userFactory);
    }

    public function testCreatingUser() {
        $user = $this->createMock(User::class);
        $this->userFactory->expects($this->once())->method('createUser')->willReturn($user);
        $command = new UserCreateCommand('JohnDoe');
        $createdUser = $this->handler->handle($command);
        $this->assertSame($user, $createdUser);
    }
}
