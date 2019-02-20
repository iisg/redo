<?php
namespace Repeka\Domain\Entity;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemRole;

abstract class User implements Identifiable {
    /** @var ResourceEntity */
    protected $userData;

    protected $roles = [];

    public function getUserData(): ResourceEntity {
        return $this->userData;
    }

    public function getUserResourceId(): int {
        return $this->userData->getId();
    }

    public function setUserData(ResourceEntity $userData) {
        $this->userData = $userData;
    }

    public function getUsername(): string {
        return $this->getUserData()->getValues(SystemMetadata::USERNAME)[0]->getValue();
    }

    public function getUserGroupsIds(): array {
        return $this->getUserData()->getContents()->getValuesWithoutSubmetadata(SystemMetadata::GROUP_MEMBER);
    }

    /**
     * @return array containing users id and ids of all groups he belongs to
     */
    public function getGroupIdsWithUserId(): array {
        return array_merge([$this->userData->getId()], $this->getUserGroupsIds());
    }

    /**
     * Tells if user belongs to any of the groups listed by id in the given array.
     * @param array $userGroupsIds groups ids to look for
     * @return bool true if user's ID of any of its group is in the given array, false otherwise
     */
    public function belongsToAnyOfGivenUserGroupsIds(array $userGroupsIds): bool {
        return !empty(array_intersect($userGroupsIds, array_merge($this->getUserGroupsIds(), [$this->getUserData()->getId()])));
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

    /** string[] */
    public function resourceClassesInWhichUserHasRole(SystemRole $role): array {
        $roleRegex = $role->roleName('(.+)');
        $classesInWhichUserHasRole = array_map(
            function (string $userRole) use ($roleRegex) {
                preg_match("#$roleRegex#", $userRole, $matches);
                return $matches ? $matches[1] : null;
            },
            $this->getRoles()
        );
        return array_values(array_filter($classesInWhichUserHasRole));
    }

    public function hasRole(string $roleName): bool {
        return in_array($roleName, $this->getRoles());
    }

    public function updateRoles(array $roles): void {
        $this->roles = array_values($roles);
    }
}
