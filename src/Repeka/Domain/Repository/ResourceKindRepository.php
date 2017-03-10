<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;

interface ResourceKindRepository {
    /**
     * @return ResourceKind[]
     */
    public function findAll();

    /**
     * @throws EntityNotFoundException if the entity could not be found
     */
    public function findOne(int $id): ResourceKind;

    public function save(ResourceKind $resourceKind): ResourceKind;

    public function exists(int $id): bool;
}
