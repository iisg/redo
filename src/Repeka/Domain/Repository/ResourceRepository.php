<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\TreeResult;

interface ResourceRepository {
    /** @return ResourceEntity[] */
    public function findAll();

    /**
     * @throws EntityNotFoundException if the entity could not be found
     */
    public function findOne(int $id): ResourceEntity;

    public function save(ResourceEntity $resource): ResourceEntity;

    public function exists(int $resourceId): bool;

    public function delete(ResourceEntity $resource): void;

    /** @return int */
    public function count(array $criteria);

    public function countByResourceKind(ResourceKind $resourceKind): int;

    /** @return PageResult|ResourceEntity[] */
    public function findByQuery(ResourceListQuery $query): PageResult;

    /** @return TreeResult */
    public function findByTreeQuery(ResourceTreeQuery $query): TreeResult;

    /** @return ResourceEntity[] */
    public function findUsersInGroup(ResourceEntity $userGroup): array;

    /** @return ResourceEntity[] */
    public function findByDisplayStrategyDependencies(ResourceEntity $resource, array $changedMetadataIds): array;

    /** @param ResourceEntity|ResourceEntity[]|ResourceKind|ResourceKind[] $resources */
    public function markDisplayStrategiesDirty($resources): void;

    public function hasChildren(ResourceEntity $resource): bool;

    /** @return ResourceEntity[] */
    public function getResourcesWithPendingUpdates(int $limit): array;
}
