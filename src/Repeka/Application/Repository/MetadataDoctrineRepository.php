<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataDoctrineRepository extends EntityRepository implements MetadataRepository {
    public function save(Metadata $metadata): Metadata {
        $this->getEntityManager()->persist($metadata);
        return $metadata;
    }

    public function findOne(int $id): Metadata {
        $metadata = $this->find($id);
        if (!$metadata) {
            throw new EntityNotFoundException("ID: $id");
        }
        return $metadata;
    }

    public function findAllBase(): array {
        return $this->findBy(['baseMetadata' => null], ['ordinalNumber' => 'ASC']);
    }
}
