<?php
namespace Repeka\Tests\Application\Elasticsearch;

use Repeka\Application\Command\Initialization\SystemUserRolesInitializer;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\UserRoleRepository;
use Symfony\Component\Console\Output\OutputInterface;

class SystemUserRolesInitializerTest extends \PHPUnit_Framework_TestCase {
    private $output;
    /** @var UserRoleRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userRoleRepository;
    /** @var SystemUserRolesInitializer */
    private $systemUserRolesInitializer;

    protected function setUp() {
        $this->output = $this->createMock(OutputInterface::class);
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $this->systemUserRolesInitializer = new SystemUserRolesInitializer();
    }

    public function testAdminRoleExists() {
        $this->userRoleRepository->expects($this->atLeastOnce())->method('exists')->willReturn(true);
        $this->userRoleRepository->expects($this->never())->method('save');
        $this->systemUserRolesInitializer->addSystemUserRoles($this->output, $this->userRoleRepository, 'pl');
    }

    public function testAdminRoleIsCreatedByCommand() {
        $this->userRoleRepository->expects($this->atLeastOnce())->method('exists')->willReturnCallback(function (string $id) {
            return $id != SystemUserRole::ADMIN;
        });
        $this->userRoleRepository->expects($this->once())->method('save')->willReturnCallback(function (UserRole $userRole) {
            $this->assertEquals(SystemUserRole::ADMIN, $userRole->getId());
            $this->assertEquals(['PL' => 'Admin'], $userRole->getName());
            $this->assertTrue($userRole->isSystemRole());
            return $userRole;
        });
        $this->systemUserRolesInitializer->addSystemUserRoles($this->output, $this->userRoleRepository, 'pl');
    }
}
