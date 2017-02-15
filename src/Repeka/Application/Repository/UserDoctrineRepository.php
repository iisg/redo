<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\EntityNotFoundException;
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

    public function save(User $user): User {
        $this->getEntityManager()->persist($user);
        return $user;
    }

    public function findOne($id): User {
        /** @var User $user */
        $user = $this->find($id);
        if (!$user) {
            throw new EntityNotFoundException($this, $id);
        }
        return $user;
    }
}
