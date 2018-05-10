<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;

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

    /**
     * @param int[] $metadataIds
     * @return Metadata[]
     */
    public function findByIds(array $metadataIds): array {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->in('id', $metadataIds));
        $result = $this->matching($criteria);
        return $result->toArray();
    }

    /** @return Metadata[] */
    public function findByQuery(MetadataListQuery $query): array {
        $criteria = Criteria::create();
        if ($query->onlyTopLevel()) {
            $criteria = $criteria->andWhere(Criteria::expr()->isNull('parentMetadata'));
        }
        if ($query->getResourceClasses()) {
            $criteria = $criteria->andWhere(Criteria::expr()->in('resourceClass', $query->getResourceClasses()));
        }
        if ($query->getControls()) {
            $controlNames = array_map(
                function (MetadataControl $control) {
                    return $control->getValue();
                },
                $query->getControls()
            );
            $criteria = $criteria->andWhere(Criteria::expr()->in('control', $controlNames));
        }
        if ($query->getParent()) {
            $criteria = $criteria->andWhere(Criteria::expr()->eq('parentMetadata', $query->getParent()));
        }
        $criteria->orderBy(['ordinalNumber' => 'ASC']);
        return $this->matching($criteria)->toArray();
    }
}
