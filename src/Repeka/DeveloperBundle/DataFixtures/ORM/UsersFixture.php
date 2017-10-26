<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;

class UsersFixture extends RepekaFixture {
    const ORDER = RolesFixture::ORDER + 1;

    const REFERENCE_USER_BUDYNEK = 'user-budynek';
    const REFERENCE_USER_TESTER = 'user-tester';
    const REFERENCE_USER_SCANNER = 'user-scanner';

    public function load(ObjectManager $manager) {
        $this->handleCommand(new UserCreateCommand('budynek', 'budynek'), self::REFERENCE_USER_BUDYNEK);
        $tester = $this->handleCommand(new UserCreateCommand('tester', 'tester'), self::REFERENCE_USER_TESTER);
        $scanner = $this->handleCommand(new UserCreateCommand('skaner', 'skaner'), self::REFERENCE_USER_SCANNER);
        $operatorRole = $manager->getRepository(UserRole::class)->findOne(SystemUserRole::OPERATOR);
        $testerRole = $this->getReference(RolesFixture::ROLE_TESTER);
        $scannerRole = $this->getReference(RolesFixture::ROLE_SCANNER);
        $this->handleCommand(new UserUpdateRolesCommand($tester, [$operatorRole, $testerRole], $tester));
        $this->handleCommand(new UserUpdateRolesCommand($scanner, [$operatorRole, $scannerRole], $tester));
    }
}
