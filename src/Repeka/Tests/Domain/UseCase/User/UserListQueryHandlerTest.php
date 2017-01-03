<?php
namespace Repeka\Tests\Domain\UseCase\User;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserListQueryHandler;

class LanguageListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $userRepository;
    /** @var UserListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->handler = new UserListQueryHandler($this->userRepository);
    }

    public function testGettingTheList() {
        $userList = [$this->createMock(User::class)];
        $this->userRepository->expects($this->once())->method('findAll')->willReturn($userList);
        $returnedList = $this->handler->handle();
        $this->assertSame($userList, $returnedList);
    }
}
