<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Repeka\Domain\Constants\SystemUserRole;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;

class UsersFixture extends RepekaFixture {
    const ORDER = RolesFixture::ORDER + 1;

    public function load(ObjectManager $manager) {
        /** @var CommandBus $commandBus */
        $commandBus = $this->container->get('repeka.command_bus');

        $commandBus->handle(new UserCreateCommand('budynek', 'budynek', 'budynek@repeka.dev'));

        $commandBus->handle(new UserCreateCommand('tester', 'tester', 'tester@repeka.dev'));
        $user = $manager->getRepository(UserEntity::class)->findBy(['username' => 'tester'])[0];
        $operatorRole = $manager->getRepository(UserRole::class)->findOne(SystemUserRole::OPERATOR);
        $testerRole = $this->getReference(RolesFixture::ROLE_TESTER);
        $commandBus->handle(new UserUpdateRolesCommand($user, [$operatorRole, $testerRole]));
    }
}
