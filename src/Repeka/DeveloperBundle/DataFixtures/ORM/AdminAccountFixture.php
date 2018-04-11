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
        $admin = $container->get(UserRepository::class)->loadUserByUsername(self::USERNAME);
        if (!$admin) {
            $userCreateCommand = new UserCreateCommand(self::USERNAME, self::PASSWORD);
            $admin = $this->handleCommand($userCreateCommand);
            $allUserRoles = $this->handleCommand(new UserRoleListQuery());
            $userUpdateRolesCommand = new UserUpdateRolesCommand($admin, $allUserRoles, $admin);
            $this->handleCommand($userUpdateRolesCommand);
        }
        $this->addReference(self::REFERENCE_USER_ADMIN, $admin);
    }
}
