<?php
namespace Repeka\Application\Repository;

use Repeka\Domain\Entity\UserRole;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\UserRoleRepository;

class UserRoleDoctrineRepository extends AbstractRepository implements UserRoleRepository {
    public function save(UserRole $role): UserRole {
        return $this->persist($role);
    }

    public function findOne(string $id): UserRole {
        return $this->findById($id);
    }

    public function exists(string $id): bool {
        try {
            $this->findOne($id);
            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }
}
