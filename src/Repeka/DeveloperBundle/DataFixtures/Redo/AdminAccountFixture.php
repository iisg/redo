<?php
namespace Repeka\DeveloperBundle\DataFixtures\Redo;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Repeka\DeveloperBundle\DataFixtures\RepekaFixture;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AdminAccountFixture extends RepekaFixture {
    const USERNAME = 'admin';
    const PASSWORD = 'admin';
    const REFERENCE_USER_ADMIN = 'user-admin';

    /**
     * By setting a custom user id sequence start we ensure that users from fixtures get high ids so they are not confused with resource's
     * ids in integration tests. For example, now admin user (id 1038) will have user data as resource (probably id 1). Previously, there
     * was the same id 1 for both which led to some false assumptions in tests
     */
    const ADMIN_USER_ID = 1038;

    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $containerInterface */
        $container = $this->container;
        $admin = $container->get(UserRepository::class)->loadUserByUsername(self::USERNAME);
        if (!$admin) {
            $this->container->get(EntityManagerInterface::class)->getConnection()
                ->executeQuery('ALTER SEQUENCE user_id_seq RESTART WITH ' . self::ADMIN_USER_ID);
            $userCreateCommand = new UserCreateCommand(self::USERNAME, self::PASSWORD);
            $admin = $this->handleCommand($userCreateCommand);
        }
        $this->addReference(self::REFERENCE_USER_ADMIN, $admin);
    }
}
