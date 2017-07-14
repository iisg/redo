<?php

namespace Repeka\Tests\Application\Elasticsearch;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Command\Initialization\InitializeSystemUserRolesCommand;
use Repeka\Application\Entity\EntityIdGeneratorHelper;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Repository\LanguageRepository;
use Repeka\Domain\Repository\UserRoleRepository;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeSystemUserRolesCommandTest extends \PHPUnit_Framework_TestCase {
    private $output;
    /** @var UserRoleRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userRoleRepository;
    /** @var InitializeSystemUserRolesCommand */
    private $command;

    protected function setUp() {
        $this->output = $this->createMock(OutputInterface::class);
        $idGeneratorHelper = $this->createMock(EntityIdGeneratorHelper::class);
        $this->userRoleRepository = $this->createMock(UserRoleRepository::class);
        $languageRepository = $this->createMock(LanguageRepository::class);
        $this->command = new InitializeSystemUserRolesCommand($idGeneratorHelper, $this->userRoleRepository, $languageRepository);
    }

    public function testAdminRoleExists() {
        $this->userRoleRepository->expects($this->atLeastOnce())->method('exists')->willReturn(true);
        $this->userRoleRepository->expects($this->never())->method('save');
        $this->command->addSystemUserRoles($this->output, 'PL');
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
        $this->command->addSystemUserRoles($this->output, 'PL');
    }
}
