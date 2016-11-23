<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\EntityNotFoundException;

interface MetadataRepository {
    /**
     * @return Metadata[]
     */
    public function findAll();

    public function save(Metadata $metadata): Metadata;

    /**
     * @throws EntityNotFoundException if the entity could not be found
     */
    public function findOne(int $id): Metadata;

    /** @return Metadata[] */
    public function findAllBase(): array;
}
