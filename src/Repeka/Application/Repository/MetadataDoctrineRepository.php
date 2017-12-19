<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
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

    public function findByName(string $name): Metadata {
        /** @var Metadata $metadata */
        $metadata = $this->findOneBy(['name' => $name]);
        if (!$metadata) {
            throw new EntityNotFoundException($this, $name);
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

    public function findAllBaseByResourceClass(string $resourceClass): array {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull('baseMetadata'))
            ->andWhere(Criteria::expr()->isNull('parentMetadata'))
            ->andWhere(Criteria::expr()->eq('resourceClass', $resourceClass))
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

    public function delete(Metadata $metadata): void {
        $this->getEntityManager()->remove($metadata);
    }

    public function countByParent(Metadata $parent): int {
        $qb = $this->createQueryBuilder('m');
        $query = $qb->select('COUNT(m.id)')
            ->where('m.parentMetadata = :parent')
            ->setParameter('parent', $parent)
            ->getQuery();
        return $query->getSingleScalarResult();
    }

    public function countByBase(Metadata $base): int {
        $qb = $this->createQueryBuilder('m');
        $query = $qb->select('COUNT(m.id)')
            ->where('m.baseMetadata = :base')
            ->setParameter('base', $base)
            ->getQuery();
        return $query->getSingleScalarResult();
    }
}
