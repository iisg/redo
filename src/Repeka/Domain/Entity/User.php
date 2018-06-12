<?php
namespace Repeka\Domain\Entity;

use Repeka\Domain\Constants\SystemMetadata;

abstract class User implements Identifiable {
    /** @var ResourceEntity */
    protected $userData;

    protected $roles = [];

    public function getUserData(): ResourceEntity {
        return $this->userData;
    }

    public function setUserData(ResourceEntity $userData) {
        $this->userData = $userData;
    }

    public function getUsername(): string {
        return $this->getUserData()->getValues(SystemMetadata::USERNAME)[0];
    }

    public function getUserGroupsIds(): array {
        return $this->getUserData()->getValues(SystemMetadata::GROUP_MEMBER);
    }

    public function setUsername(string $username): User {
        $contents = $this->getUserData()->getContents();
        $contents = $contents->withReplacedValues(SystemMetadata::USERNAME, $username);
        $this->getUserData()->updateContents($contents);
        return $this;
    }

    public function getRoles(): array {
        return $this->roles ?? [];
    }

    public function updateRoles(array $roles): void {
        $this->roles = array_values($roles);
    }
}
