<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Factory\MetadataListQuerySqlFactory;
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
        $query = MetadataListQuery::builder()->filterByName($name);
        $result = $this->findByQuery($query->build());
        if (!$result) {
            throw new EntityNotFoundException($this, $name);
        }
        return $result[0];
    }

    public function findByNameOrId($nameOrId): Metadata {
        if (is_numeric($nameOrId)) {
            return $this->findOne($nameOrId);
        } else {
            return $this->findByName($nameOrId);
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
        $queryFactory = new MetadataListQuerySqlFactory($query);
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::Metadata($em);
        $dbQuery = $em->createNativeQuery($queryFactory->getPageQuery(), $resultSetMapping)->setParameters($queryFactory->getParams());
        return $dbQuery->getResult();
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
