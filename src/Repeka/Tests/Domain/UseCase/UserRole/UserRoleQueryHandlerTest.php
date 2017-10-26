<?php
namespace Repeka\Tests\Domain\UseCase\UserRole;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\UserRoleRepository;
use Repeka\Domain\UseCase\UserRole\UserRoleQuery;
use Repeka\Domain\UseCase\UserRole\UserRoleQueryHandler;

class UserRoleQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|UserRoleRepository */
    private $userRoleRepository;

    /** @var UserRoleQueryHandler */
    private $handler;

    protected function setUp() {
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->handler = new UserRoleQueryHandler($this->userRoleRepository);
    }

    public function testHandling() {
        $this->userRoleRepository->expects($this->once())->method('findOne')->with(2);
        $this->handler->handle(new UserRoleQuery(2));
    }
}
