<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceDoctrineRepository extends EntityRepository implements ResourceRepository {
    public function __construct(EntityManagerInterface $em, ClassMetadata $class) {
        parent::__construct($em, $class);
    }

    public function save(ResourceEntity $resource): ResourceEntity {
        $this->getEntityManager()->persist($resource);
        return $resource;
    }

    public function findOne(int $id): ResourceEntity {
        /** @var ResourceEntity $resource */
        $resource = $this->find($id);
        if (!$resource) {
            throw new EntityNotFoundException($this, $id);
        }
        return $resource;
    }

    /** @return ResourceEntity[] */
    public function findTopLevel(): array {
        $parentMetadataId = SystemMetadata::PARENT;
        $qb = $this->createQueryBuilder('r');
        return $qb->join('r.kind', 'rk')
            ->where("JSON_GET(r.contents, '$parentMetadataId') = '[]' OR JSONB_EXISTS(r.contents, '$parentMetadataId') = FALSE")
            ->andWhere($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->getQuery()
            ->getResult();
    }

    /** @return ResourceEntity[] */
    public function findChildren(int $parentId): array {
        $parentMetadataId = SystemMetadata::PARENT;
        return $this->createQueryBuilder('r')
            ->where("JSONB_CONTAINS(JSON_GET(r.contents, '$parentMetadataId'), :parentId) = TRUE")
            ->setParameter('parentId', $parentId)
            ->getQuery()
            ->getResult();
    }

    /** @return ResourceEntity[] */
    public function findAllNonSystemResources(): array {
        $qb = $this->createQueryBuilder('r');
        return $qb->join('r.kind', 'rk')
            ->where($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->getQuery()
            ->getResult();
    }

    public function exists(int $resourceId): bool {
        return !!$this->find($resourceId);
    }

    public function delete(ResourceEntity $resource): void {
        $this->getEntityManager()->remove($resource);
    }

    public function countByResourceKind(ResourceKind $resourceKind): int {
        $qb = $this->createQueryBuilder('r');
        $query = $qb->select('COUNT(r.id)')
            ->where('r.kind = :kind')
            ->setParameter('kind', $resourceKind)
            ->getQuery();
        return $query->getSingleScalarResult();
    }

    public function findAssignedTo(User $user): array {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceEntity($em);
        $query = $em->createNativeQuery(<<<SQL
SELECT *
FROM resource
WHERE id IN (
  SELECT resource_id
  FROM (
         SELECT
           resource_id,
           resource_contents -> metadata_id :: CHAR AS assignee_ids
         FROM (
                SELECT
                  jsonb_array_elements(place -> 'assigneeMetadataIds') AS metadata_id,
                  resource_id,
                  resource_contents
                FROM (
                       SELECT
                         jsonb_array_elements(places) AS place,
                         resource.id                  AS resource_id,
                         resource.contents            AS resource_contents
                       FROM workflow
                         LEFT JOIN resource_kind ON workflow.id = resource_kind.workflow_id
                         LEFT JOIN resource ON resource_kind.id = resource.kind_id
                     ) AS with_places_as_rows
              ) AS with_metadata
       ) AS with_assignee_ids
  WHERE assignee_ids @> :userId :: CHAR :: JSONB
)
SQL
            , $resultSetMapping);
        $query->setParameter('userId', $user->getUserData());
        return $query->getResult();
    }
}
