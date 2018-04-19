<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Factory\ResourceListQuerySqlFactory;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceDoctrineRepository extends EntityRepository implements ResourceRepository {
    /** @var UserRepository */
    private $userRepository;

    /** @required */
    public function setUserRepository(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
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

    /**
     * @return ResourceEntity[]
     */
    public function findByQuery(ResourceListQuery $query): PageResult {
        $queryFactory = new ResourceListQuerySqlFactory($query);
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceEntity($em);
        $dbQuery = $em->createNativeQuery($queryFactory->getPageQuery(), $resultSetMapping)->setParameters($queryFactory->getParams());
        $pageContents = $dbQuery->getResult();
        $total = $em->createNativeQuery($queryFactory->getTotalCountQuery(), ResultSetMappings::scalar())
            ->setParameters($queryFactory->getParams());
        $total = count($total->getScalarResult());
        return new PageResult($pageContents, $total, $query->getPage());
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
        $query = $em->createNativeQuery(
            <<<SQL
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
WHERE assignee_id IN(:userIds)
SQL
            ,
            $resultSetMapping
        );
        $groups = $this->userRepository->findUserGroups($user);
        $groups[] = $user->getUserData();
        $query->setParameter('userIds', EntityUtils::mapToIds($groups));
        return $query->getResult();
    }
}
