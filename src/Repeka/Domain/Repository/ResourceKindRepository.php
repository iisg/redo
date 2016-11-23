<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\ResourceKind;

interface ResourceKindRepository {
    /**
     * @return ResourceKind[]
     */
    public function findAll();

    public function save(ResourceKind $resourceKind): ResourceKind;
}
