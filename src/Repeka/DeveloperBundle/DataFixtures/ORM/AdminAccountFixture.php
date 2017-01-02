<?php
namespace Repeka\DeveloperBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Repeka\Application\Entity\UserEntity;
use Symfony\Bridge\Doctrine\Tests\Fixtures\ContainerAwareFixture;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class AdminAccountCreator extends ContainerAwareFixture {
    public function load(ObjectManager $manager) {
        $user = new UserEntity();
        $user->setUsername('admin');
        $user->setEmail('admin');
        $user->setName('Aleksander');
        $user->setSurname('Wszystkomogący');
        /** @var PasswordEncoderInterface $encoder */
        $encoder = $this->container->get('security.password_encoder');
        $password = $encoder->encodePassword($user, 'admin');
        $user->setPassword($password);
        $user->updateStaticPermissions($this->container->getParameter('repeka.static_permissions'));
        $manager->persist($user);
        $manager->flush();
    }
}
