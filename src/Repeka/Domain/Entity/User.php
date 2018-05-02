<?php
namespace Repeka\Domain\Entity;

use Repeka\Domain\Constants\SystemMetadata;

abstract class User implements Identifiable {
    /** @var ResourceEntity */
    protected $userData;

    abstract public function getId(): int;

    /** @param $roles UserRole */
    abstract public function updateRoles(array $roles): void;

    /** @return UserRole[] */
    abstract public function getUserRoles(): array;

    /**
     * @param UserRole|string $role
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

    public function getUsername(): string {
        return $this->getUserData()->getValues(SystemMetadata::USERNAME)[0];
    }

    public function setUsername(string $username): User {
        $contents = $this->getUserData()->getContents();
        $contents = $contents->withReplacedValues(SystemMetadata::USERNAME, $username);
        $this->getUserData()->updateContents($contents);
        return $this;
    }
}
