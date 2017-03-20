<?php
namespace Repeka\Application\Repository;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserDoctrineRepository extends AbstractRepository implements UserRepository, UserLoaderInterface {
    public function loadUserByUsername($username) {
        return $this->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(User $user): User {
        return $this->persist($user);
    }

    public function findOne(int $id): User {
        return $this->findById($id);
    }
}
