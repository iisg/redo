<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;

class UsersFixture extends RepekaFixture {
    const ORDER = RolesFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        $this->handleCommand(new UserCreateCommand('budynek', 'budynek', 'budynek@repeka.dev'));
        $this->handleCommand(new UserCreateCommand('tester', 'tester', 'tester@repeka.dev'));
        $user = $manager->getRepository(UserEntity::class)->findBy(['username' => 'tester'])[0];
        $operatorRole = $manager->getRepository(UserRole::class)->findOne(SystemUserRole::OPERATOR);
        $testerRole = $this->getReference(RolesFixture::ROLE_TESTER);
        $this->handleCommand(new UserUpdateRolesCommand($user, [$operatorRole, $testerRole]));
    }
}
