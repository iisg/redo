<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataDoctrineRepository extends EntityRepository implements MetadataRepository {
    public function save(Metadata $metadata): Metadata {
        $this->getEntityManager()->persist($metadata);
        return $metadata;
    }
}
