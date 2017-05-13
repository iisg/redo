<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Domain\UseCase\User\UserCreateCommand;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\DependencyInjection\ContainerInterface;

class UsersFixture extends ContainerAwareFixture {
    /**
     * @inheritdoc
     */
    public function load(ObjectManager $manager) {
        /** @var ContainerInterface $containerInterface */
        $container = $this->container;
        $userCreateCommand = new UserCreateCommand('budynek', 'budynek', 'budynek@repeka.pl');
        $container->get('repeka.command_bus')->handle($userCreateCommand);
    }
}
