<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Repeka\Domain\UseCase\User\UserUpdateRolesCommand;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminAccountFixture extends ContainerAwareFixture {
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $containerInterface */
        $container = $this->container;
        if (!$container->get('repository.user')->loadUserByUsername(self::USERNAME)) {
            $userCreateCommand = new UserCreateCommand(self::USERNAME, self::PASSWORD, 'admin@repeka.pl');
            $user = $container->get('repeka.command_bus')->handle($userCreateCommand);
            $userUpdateRolesCommand = new UserUpdateRolesCommand($user, $container->get('repository.user_role')->findAll());
            $container->get('repeka.command_bus')->handle($userUpdateRolesCommand);
        }
    }
}
