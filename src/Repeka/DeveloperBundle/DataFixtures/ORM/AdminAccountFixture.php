<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Repeka\Domain\UseCase\UserRole\UserRoleListQuery;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminAccountFixture extends RepekaFixture {
    const USERNAME = 'admin';
    const PASSWORD = 'admin';
    const REFERENCE_USER_ADMIN = 'user-admin';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $containerInterface */
        $container = $this->container;
        if (!$container->get(UserRepository::class)->loadUserByUsername(self::USERNAME)) {
            $userCreateCommand = new UserCreateCommand(self::USERNAME, self::PASSWORD);
            $user = $this->handleCommand($userCreateCommand, self::REFERENCE_USER_ADMIN);
            $allUserRoles = $this->handleCommand(new UserRoleListQuery());
            $userUpdateRolesCommand = new UserUpdateRolesCommand($user, $allUserRoles, $user);
            $this->handleCommand($userUpdateRolesCommand);
        }
    }
}
