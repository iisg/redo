<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
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

    public function findByName(string $name, ?string $resourceClass = null): Metadata {
        $query = MetadataListQuery::builder()->filterByNames([$name]);
        if ($resourceClass) {
            $query = $query->filterByResourceClass($resourceClass);
        }
        $result = $this->findByQuery($query->build());
        if (!$result) {
            throw new EntityNotFoundException($this, $name);
        }
        return $result[0];
    }

    public function findByNameOrId($nameOrId, ?string $resourceClass = null): Metadata {
        if (is_numeric($nameOrId)) {
            return $this->findOne($nameOrId);
        } else {
            return $this->findByName($nameOrId, $resourceClass);
        }
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
        if ($query->getIds()) {
            $criteria = $criteria->andWhere(Criteria::expr()->in('id', $query->getIds()));
        }
        if ($query->getSystemMetadataIds()) {
            $criteria = $criteria->orWhere(Criteria::expr()->in('id', $query->getSystemMetadataIds()));
        }
        if ($query->getNames()) {
            $names = array_map([Metadata::class, 'normalizeMetadataName'], $query->getNames());
            $criteria = $criteria->andWhere(Criteria::expr()->in('name', $names));
        }
        $criteria->orderBy(['ordinalNumber' => 'ASC']);
        return $this->matching($criteria)->toArray();
    }

    public function removeResourceKindFromMetadataConstraints(ResourceKind $resourceKind): void {
        $resourceKindId = $resourceKind->getId();
        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery(
            <<<SQL
           UPDATE metadata editedMetadata
           SET constraints = jsonb_set(constraints,
                                       '{resourceKind}',
                        (SELECT to_jsonb(array_remove(array_agg(resourceKind), :resourceKindId)::int[])
                         FROM (
                          SELECT resourceKind
                          FROM (
                                 SELECT jsonb_array_elements_text(constraints -> 'resourceKind') AS resourceKind
                                 FROM metadata
                                 WHERE id = editedMetadata.id) AS resourceKinds
                        ) AS resourceKindIds)::jsonb
                        )
           WHERE :resourceKindId in (select jsonb_array_elements(constraints -> 'resourceKind') from metadata where editedMetadata.id = id)
SQL
            ,
            $rsm
        );
        $query->setParameter('resourceKindId', $resourceKindId);
        $query->execute();
    }
}
