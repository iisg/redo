<?php
namespace Repeka\DataModule\Domain\Repository;

use Repeka\DataModule\Domain\Entity\Metadata;

interface MetadataRepository {
    /**
     * @return Metadata[]
     */
    public function findAll();

    public function save(Metadata $metadata): Metadata;
}
