<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\Metadata;

interface MetadataRepository {
    /**
     * @return Metadata[]
     */
    public function findAll();

    public function save(Metadata $metadata): Metadata;
}
