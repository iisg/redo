<?php
namespace Repeka\Tests\Domain\UseCase\User;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\User\UserListQueryHandler;
use Repeka\Domain\UseCase\UserRole\UserRoleListQueryHandler;

class UserRoleListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $userRoleRepository;
    /** @var UserListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->handler = new UserRoleListQueryHandler($this->userRoleRepository);
    }

    public function testGettingTheList() {
        $roleList = [$this->createMock(UserRole::class)];
        $this->userRoleRepository->expects($this->once())->method('findAll')->willReturn($roleList);
        $returnedList = $this->handler->handle();
        $this->assertSame($roleList, $returnedList);
    }
}
