<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class UsersFixture extends ContainerAwareFixture {
    public function load(ObjectManager $manager) {
        $user = new UserEntity();
        $user->setUsername('budynek');
        $user->setEmail('budynek');
        $user->setFirstname('Piotr');
        $user->setLastname('Budynek');
        /** @var PasswordEncoderInterface $encoder */
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, 'budynek');
        $user->setPassword($password);
        $manager->persist($user);
        $manager->flush();
    }
}
