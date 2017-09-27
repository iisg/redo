<?php
namespace Repeka\Domain\Entity;

abstract class User implements Identifiable {
    /** @var ResourceEntity */
    protected $userData;

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

    public function getUserData(): ResourceEntity {
        return $this->userData;
    }

    public function setUserData(ResourceEntity $userData) {
        $this->userData = $userData;
    }

    abstract public function getUsername(): string;

    abstract public function getEmail(): ?string;
}
