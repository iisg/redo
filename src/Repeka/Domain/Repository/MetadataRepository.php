<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\EntityNotFoundException;

interface MetadataRepository {
    /**@return Metadata[] */
    public function findAll();

    public function save(Metadata $metadata): Metadata;

    /**
     * @throws EntityNotFoundException if the entity could not be found
     */
    public function findOne(int $id): Metadata;

    /**
     * Returns all metadata that have no base (i.e. are not assigned to any resoruce kind), sorted by ordinalNumber, ascending.
     * @return Metadata[]
     */
    public function findAllBase(): array;

    /**
     * Returns metadata by resource class that have no base(i.e. are not assigned to any resoruce kind), sorted by ordinalNumber, ascending.
     * @return Metadata[] */
    public function findAllBaseByResourceClass(string $resourceClass): array;

    /**
     * Returns all metadata that have specific parent ID.
     * @return Metadata[]
     */
    public function findAllChildren(int $parentId): array;

    public function exists(int $id): bool;

    public function delete(Metadata $metadata): void;

    public function countByParent(Metadata $parent): int;

    public function countByBase(Metadata $base): int;
}
