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
     * @throws EntityNotFoundException if the entity could not be found
     */
    public function findByName(string $name): Metadata;

    /**
     * Returns metadata without parent by given resource class sorted by ordinalNumber, ascending.
     * @return Metadata[]
     */
    public function findTopLevelByResourceClass(string $resourceClass): array;

    /** @return Metadata[] */
    public function findByParent(Metadata $parent): array;

    public function exists(int $id): bool;

    public function delete(Metadata $metadata): void;

    public function countByParent(Metadata $parent): int;

    /**
     * @param int[] $metadataIds
     * @return Metadata[]
     */
    public function findByIds(array $metadataIds): array;
}
