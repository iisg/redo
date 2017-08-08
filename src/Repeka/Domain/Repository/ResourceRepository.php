<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;

interface ResourceRepository {
    /** @return ResourceEntity[] */
    public function findAll();

    public function findOne(int $id): ResourceEntity;

    /** @return ResourceEntity[] */
    public function findTopLevel(): array;

    /** @return ResourceEntity[] */
    public function findChildren(int $parentId): array;

    public function save(ResourceEntity $resource): ResourceEntity;

    /** @return ResourceEntity[] */
    public function findAllNonSystemResources(): array;

    public function exists(int $resourceId): bool;

    public function delete(ResourceEntity $resource): void;
}
