<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Factory\ResourceKindListQuerySqlFactory;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;

class ResourceKindDoctrineRepository extends EntityRepository implements ResourceKindRepository {
    public function save(ResourceKind $resourceKind): ResourceKind {
        $this->getEntityManager()->persist($resourceKind);
        // the following line is required to fire a preUpdate event if only the (not persisted) metadataList has been changed
        // see: https://stackoverflow.com/a/42907345/878514
        $this->getEntityManager()->getUnitOfWork()->scheduleForUpdate($resourceKind);
        return $resourceKind;
    }

    /** @return ResourceKind[] */
    public function findAllSystemResourceKinds(): array {
        $criteria = Criteria::create()
            ->orWhere(Criteria::expr()->lt('id', 0));
        $result = $this->matching($criteria);
        return $result->toArray();
    }

    public function findOne(int $id): ResourceKind {
        /** @var ResourceKind $resourceKind */
        $resourceKind = $this->find($id);
        if (!$resourceKind) {
            throw new EntityNotFoundException($this, $id);
        }
        return $resourceKind;
    }

    public function exists(int $id): bool {
        return !!$this->find($id);
    }

    public function delete(ResourceKind $resourceKind): void {
        $this->getEntityManager()->remove($resourceKind);
    }

    public function countByResourceWorkflow(ResourceWorkflow $resourceWorkflow): int {
        $qb = $this->createQueryBuilder('rk');
        $query = $qb->select('COUNT(rk.id)')
            ->where('rk.workflow = :workflow')
            ->setParameter('workflow', $resourceWorkflow)
            ->getQuery();
        return $query->getSingleScalarResult();
    }

    public function countByMetadata(Metadata $metadata): int {
        $qb = $this->createQueryBuilder('rk');
        $query = $qb->select('COUNT(rk.id)')
            ->where("CONTAINS(rk.metadataOverrides, :searchValue) = TRUE")
            ->setParameter('searchValue', json_encode([['id' => $metadata->getId()]]))
            ->getQuery();
        return $query->getSingleScalarResult();
    }

    /** @return ResourceKind[] */
    public function findByQuery(ResourceKindListQuery $query): array {
        $queryFactory = new ResourceKindListQuerySqlFactory($query);
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceKind($em);
        $dbQuery = $em->createNativeQuery($queryFactory->getQuery(), $resultSetMapping)->setParameters($queryFactory->getParams());
        return $dbQuery->getResult();
    }

    public function removeEveryResourceKindsUsageInOtherResourceKinds(ResourceKind $resourceKind): void {
        $resourceKindId = $resourceKind->getId();
        $rsm = new ResultSetMapping();
        $query = $this->getEntityManager()->createNativeQuery(
            <<<SQL
        UPDATE resource_kind rk
        SET metadata_list =
        (
          SELECT to_jsonb(array_agg(metadata))
          FROM (
                 SELECT CASE WHEN (metadata #> '{constraints, resourceKind}') @> :resourceKindId = TRUE
                     THEN
                       jsonb_set(metadata,
                           '{constraints, resourceKind}',
                           (SELECT to_jsonb(array_remove(array_agg(resourceKindId), :resourceKindId) :: INT [])
                            FROM (
                                  SELECT jsonb_array_elements_text(metadata #> '{constraints, resourceKind}') AS resourceKindId
                                  FROM (
                                         SELECT jsonb_array_elements(metadata_list) AS editedMetadata
                                         FROM resource_kind
                                         WHERE id = rk.id) AS jsonMetadata
                                  WHERE editedMetadata = metadata) AS resourcekindIds
                           ),
                           FALSE
                       )
                       ELSE metadata
                       END AS metadata
                 FROM (
                        SELECT jsonb_array_elements(metadata_list) AS metadata
                        FROM resource_kind
                        WHERE id = rk.id
                 ) AS metadatas
          ) AS fixedMetadatas
        )
        WHERE :resourceKindId IN (
          SELECT jsonb_array_elements((jsonb_array_elements(metadata_list)) #> '{constraints, resourceKind}')
          FROM resource_kind
          WHERE rk.id = id);
SQL
            ,
            $rsm
        );
        $query->setParameter('resourceKindId', $resourceKindId);
        $query->execute();
    }
}
