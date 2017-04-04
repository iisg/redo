<?php
namespace Repeka\Domain\Entity;

abstract class User {
    abstract public function getId(): int;

    /** @param $roles UserRole */
    abstract public function updateRoles(array $roles): void;

    abstract public function getUserRoles(): array;

    public function hasRole(UserRole $role): bool {
        foreach ($this->getUserRoles() as $r) {
            if ($r->getId() === $role->getId()) {
                return true;
            }
        }
        return false;
    }
}
