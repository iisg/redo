<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;

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

    public function exists(int $id): bool;

    public function delete(Metadata $metadata): void;

    public function countByParent(Metadata $parent): int;

    /**
     * @param int[] $metadataIds
     * @return Metadata[]
     */
    public function findByIds(array $metadataIds): array;

    /** @return Metadata[] */
    public function findByQuery(MetadataListQuery $query): array;
}
