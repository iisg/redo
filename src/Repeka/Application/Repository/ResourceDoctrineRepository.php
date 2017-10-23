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

/** @SuppressWarnings(PHPMD.TooManyPublicMethods) */
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
    public function findAllByResourceClass(string $resourceClass): array {
        $qb = $this->createQueryBuilder('r');
        return $qb->where('r.resourceClass = :resourceClass')
            ->setParameter('resourceClass', $resourceClass)
            ->getQuery()
            ->getResult();
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
    public function findAllNonSystemResources(string $resourceClass): array {
        $qb = $this->createQueryBuilder('r');
        return $qb->join('r.kind', 'rk')
            ->where($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->andWhere("r.resourceClass = :resourceClass")
            ->setParameter('resourceClass', $resourceClass)
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
-- Filters rows by user data IDs (ie. ID of user's resource, not user's entity!)
SELECT
  resources_with_assignees.*
FROM (
       -- Picks only metadata_id from resource_contents object
       -- Each row contains a resource ID and an array of users it's assigned to
       SELECT
         resources_with_assignee_metadata_ids.*,
         contents -> metadata_id AS assignee_ids
       FROM (
              -- Extracts arrays of assigneeMetadataIds from places and splits them into rows.
              -- Rows are basically a cross join of resources with all assigneeMetadataIds
              SELECT
                -- ->> 0 turns JSONB string to text without "quotation marks"
                jsonb_array_elements(place -> 'assigneeMetadataIds') ->> 0 AS metadata_id,
                resources_with_places.*
              FROM (
                     -- Left-joins each place in each workflow with resources using these workflows
                     -- Rows represent all possible combinations of resources and places in their workflows
                     SELECT
                       jsonb_array_elements(workflow.places) AS place,
                       resource.*
                     FROM workflow
                       LEFT JOIN resource_kind ON workflow.id = resource_kind.workflow_id
                       LEFT JOIN resource ON resource_kind.id = resource.kind_id
                   ) AS resources_with_places
            ) AS resources_with_assignee_metadata_ids
     ) AS resources_with_assignees
WHERE assignee_ids @> :userId :: TEXT :: JSONB
SQL
            , $resultSetMapping);
        $query->setParameter('userId', $user->getUserData()->getId());
        return $query->getResult();
    }
}
