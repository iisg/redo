<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\EntityUtils;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceChildrenQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
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
    public function findByQuery(ResourceListQuery $query): PageResult {
        $queryFroms = ['resource r'];
        $queryWheres = ['1=1'];
        $queryParams = [];
        if ($query->getIds()) {
            $queryWheres[] = 'id IN(:ids)';
            $queryParams['ids'] = $query->getIds();
        }
        if ($query->getResourceClasses()) {
            $queryWheres[] = 'resource_class IN(:resourceClasses)';
            $queryParams['resourceClasses'] = $query->getResourceClasses();
        }
        if ($query->getResourceKinds()) {
            $queryWheres[] = 'kind_id IN(:resourceKindIds)';
            $queryParams['resourceKindIds'] = EntityUtils::mapToIds($query->getResourceKinds());
        }
        if ($query->getParentId()) {
            $parentMetadata = SystemMetadata::PARENT;
            $queryWheres[] = "r.contents->'$parentMetadata' @> :parentSearchValue";
            $queryParams['parentSearchValue'] = json_encode([['value' => $query->getParentId()]]);
        }
        if ($query->onlyTopLevel()) {
            $parentMetadata = SystemMetadata::PARENT;
            $queryWheres[] = "(r.contents->'$parentMetadata' = '[]' OR JSONB_EXISTS(r.contents, '$parentMetadata') = FALSE)";
        }
        $query->getContentsFilter()->forEachValue(function ($value, int $metadataId) use (&$queryFroms, &$queryWheres, &$queryParams) {
            $paramName = "mFilter$metadataId";
            $queryFroms[] = "jsonb_array_elements(r.contents->'$metadataId') m$metadataId";
            $queryWheres[] = "m$metadataId->>'value' ILIKE :$paramName";
            $queryParams[$paramName] = '%' . $value . '%';
        });
        $pagination = '';
        if ($query->paginate()) {
            $offset = ($query->getPage() - 1) * $query->getResultsPerPage();
            $pagination = "LIMIT {$query->getResultsPerPage()} OFFSET $offset";
        }
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceEntity($em);
        $queryFroms = implode(', ', $queryFroms);
        $queryWheres = implode(' AND ', $queryWheres);
        $querySql = "SELECT r.* FROM $queryFroms WHERE $queryWheres ORDER BY r.id ASC $pagination";
        $dbQuery = $em->createNativeQuery($querySql, $resultSetMapping);
        $dbQuery->setParameters($queryParams);
        $pageContents = $dbQuery->getResult();
        if ($pagination) {
            $querySqlTotal = "SELECT COUNT(id) count FROM $queryFroms WHERE $queryWheres";
            $total = $em->createNativeQuery($querySqlTotal, ResultSetMappings::scalar())
                ->setParameters($queryParams)
                ->getSingleScalarResult();
        } else {
            $total = count($pageContents);
        }
        return new PageResult($pageContents, $total);
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
         jsonb_array_elements(contents -> metadata_id) ->> 'value' AS assignee_id
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
WHERE assignee_id = :userId
SQL
            , $resultSetMapping);
        $query->setParameter('userId', $user->getUserData()->getId());
        return $query->getResult();
    }
}
