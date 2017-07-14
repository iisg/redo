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

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $containerInterface */
        $container = $this->container;
        if (!$container->get(UserRepository::class)->loadUserByUsername(self::USERNAME)) {
            $userCreateCommand = new UserCreateCommand(self::USERNAME, self::PASSWORD, 'admin@repeka.pl');
            $user = $this->handleCommand($userCreateCommand);
            $allUserRoles = $this->handleCommand(new UserRoleListQuery());
            $userUpdateRolesCommand = new UserUpdateRolesCommand($user, $allUserRoles);
            $this->handleCommand($userUpdateRolesCommand);
        }
    }
}
