<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceEntity;

interface ResourceRepository {
    /**
     * @return ResourceEntity[]
     */
    public function findAll();

    public function save(ResourceEntity $resource): ResourceEntity;
}
