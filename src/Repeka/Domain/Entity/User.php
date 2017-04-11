<?php
namespace Repeka\Domain\Entity;

abstract class User {
    abstract public function getId(): int;

    /** @param $roles UserRole */
    abstract public function updateRoles(array $roles): void;

    abstract public function getUserRoles(): array;

    /**
     * @param UserRole|string $role
     * @return bool
     */
    public function hasRole($role): bool {
        if ($role instanceof UserRole) {
            $role = $role->getId();
        }
        foreach ($this->getUserRoles() as $r) {
            if ($r->getId() === $role) {
                return true;
            }
        }
        return false;
    }
}
