<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
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
        /** @var Metadata $metadata */
        $metadata = $this->find($id);
        if (!$metadata) {
            throw new EntityNotFoundException($this, $id);
        }
        return $metadata;
    }

    public function findAllBase(): array {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull('baseMetadata'))
            ->andWhere(Criteria::expr()->isNull('parentMetadata'))
            ->andWhere(Criteria::expr()->gte('id', 0))
            ->orderBy(['ordinalNumber' => 'ASC']);
        $result = $this->matching($criteria);
        return $result->toArray();
    }

    public function findAllChildren(int $parentId): array {
        return $this->findBy(['parentMetadata' => $parentId]);
    }

    public function exists(int $id): bool {
        return !!$this->find($id);
    }
}
