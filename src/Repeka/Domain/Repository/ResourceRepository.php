<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;

interface ResourceRepository {
    /** @return ResourceEntity[] */
    public function findAll();

    /** @return ResourceEntity[] */
    public function findAllByResourceClass(string $resourceClass): array;

    public function findOne(int $id): ResourceEntity;

    /** @return ResourceEntity[] */
    public function findTopLevel(): array;

    /** @return ResourceEntity[] */
    public function findChildren(int $parentId): array;

    public function save(ResourceEntity $resource): ResourceEntity;

    /** @return ResourceEntity[] */
    public function findAllNonSystemResources(string $resourceClass): array;

    public function exists(int $resourceId): bool;

    public function delete(ResourceEntity $resource): void;

    public function countByResourceKind(ResourceKind $resourceKind): int;

    /** @return ResourceEntity[] */
    public function findAssignedTo(User $user): array;
}
