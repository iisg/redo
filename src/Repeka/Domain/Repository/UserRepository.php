<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;

interface UserRepository {
    /** @return User[] */
    public function findAll();

    public function findOne(int $id): User;

    public function findByUserData(ResourceEntity $resource): User;

    public function save(User $user): User;

    /** @return ResourceEntity[] */
    public function findUserGroups(User $user): array;
}
