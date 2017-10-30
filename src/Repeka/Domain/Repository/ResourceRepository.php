<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

interface ResourceRepository {
    /** @return ResourceEntity[] */
    public function findAll();

    public function findOne(int $id): ResourceEntity;

    /** @return ResourceEntity[] */
    public function findChildren(int $parentId): array;

    public function save(ResourceEntity $resource): ResourceEntity;

    public function exists(int $resourceId): bool;

    public function delete(ResourceEntity $resource): void;

    public function countByResourceKind(ResourceKind $resourceKind): int;

    /** @return ResourceEntity[] */
    public function findAssignedTo(User $user): array;

    /** @return ResourceEntity[] */
    public function findByQuery(ResourceListQuery $query): array;
}
