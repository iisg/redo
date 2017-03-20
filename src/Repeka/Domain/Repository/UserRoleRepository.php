<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\UserRole;

interface UserRoleRepository {
    /**
     * @return UserRole[]
     */
    public function findAll();

    public function findOne(string $id): UserRole;

    public function save(UserRole $user): UserRole;

    public function exists(string $id): bool;
}
