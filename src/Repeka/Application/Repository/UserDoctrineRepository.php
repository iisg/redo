<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserDoctrineRepository extends EntityRepository implements UserRepository, UserLoaderInterface {
    public function loadUserByUsername($username) {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
