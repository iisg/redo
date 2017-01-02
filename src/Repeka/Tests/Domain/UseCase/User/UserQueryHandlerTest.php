<?php
namespace Repeka\Tests\Domain\UseCase\User;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserQuery;
use Repeka\Domain\UseCase\User\UserQueryHandler;

class UserQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|UserRepository */
    private $userRepository;

    /** @var UserQueryHandler */
    private $handler;

    protected function setUp() {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->handler = new UserQueryHandler($this->userRepository);
    }

    public function testHandling() {
        $this->userRepository->expects($this->once())->method('findOne')->with(2);
        $this->handler->handle(new UserQuery(2));
    }
}
