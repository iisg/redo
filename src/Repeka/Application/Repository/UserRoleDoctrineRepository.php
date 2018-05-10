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

    public function delete(UserRole $userRole): void {
        $this->getEntityManager()->remove($userRole);
    }

    /** @return UserRole[] */
    public function findSystemRoles(): array {
        return array_filter(
            $this->findAll(),
            function (UserRole $role) {
                return $role->isSystemRole();
            }
        );
    }
}
