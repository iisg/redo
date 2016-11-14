<?php
namespace Repeka\DataModule\Bundle\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\DataModule\Domain\Entity\Metadata;
use Repeka\DataModule\Domain\Repository\MetadataRepository;

class MetadataDoctrineRepository extends EntityRepository implements MetadataRepository {
    public function save(Metadata $metadata): Metadata {
        $this->getEntityManager()->persist($metadata);
        return $metadata;
    }
}
