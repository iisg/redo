<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class AdminAccountFixture extends ContainerAwareFixture {
    const USERNAME = 'admin';
    const PASSWORD = 'admin';

    public function load(ObjectManager $manager) {
        $user = new UserEntity();
        $user->setUsername(self::USERNAME);
        $user->setEmail('admin');
        $user->setFirstname('Aleksander');
        $user->setLastname('Wszystkomogący');
        /** @var PasswordEncoderInterface $encoder */
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, self::PASSWORD);
        $user->setPassword($password);
        $user->updateRoles($this->container->get('repository.user_role')->findAll());
        $manager->persist($user);
        $manager->flush();
    }
}
