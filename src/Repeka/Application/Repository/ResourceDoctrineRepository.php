<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Factory\ResourceListQuerySqlFactory;
use Repeka\Domain\Factory\ResourceTreeQuerySqlFactory;
use Repeka\Domain\Factory\TasksQuerySqlFactory;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Service\ResourceDisplayStrategyDependencyMap;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\TreeResult;
use Repeka\Domain\Utils\EntityUtils;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ResourceDoctrineRepository extends EntityRepository implements ResourceRepository {
    /** @var UserRepository */
    private $userRepository;
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;

    /** @required */
    public function setUserRepository(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    /** @required */
    public function setWorkflowRepository(ResourceWorkflowRepository $workflowRepository) {
        $this->workflowRepository = $workflowRepository;
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
        return new PageResult($pageContents, (int)$total->getSingleScalarResult(), $query->getPage());
    }

    public function findByTreeQuery(ResourceTreeQuery $query): TreeResult {
        $queryFactory = new ResourceTreeQuerySqlFactory($query);
        /** @var ResourceEntity[] $allResources */
        $treeResources = $this->getResources($queryFactory, $query->getRootId(), $query->hasSiblings() || $query->paginate());
        $matchingResources = count($treeResources)
            ? $this->getMatchingResources($queryFactory, $treeResources)
            : [];
        return new TreeResult($treeResources, $matchingResources, $query->getPage());
    }

    /** @return ResourceEntity[] */
    private function getResources(ResourceTreeQuerySqlFactory $queryFactory, $rootId, $fixFragments) {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceEntity($em);
        $dbQuery = $em->createNativeQuery($queryFactory->getTreeQuery(), $resultSetMapping)->setParameters($queryFactory->getParams());
        $resources = $dbQuery->getResult();
        return $fixFragments
            ? $this->filterOneConnectedComponent($resources, $rootId)
            : $resources;
    }

    /** @return int[] */
    private function getMatchingResources(ResourceTreeQuerySqlFactory $queryFactory, $resources) {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::scalar('id');
        $ids = EntityUtils::mapToIds($resources);
        $dbQuery = $em->createNativeQuery($queryFactory->getMatchingResourcesQuery($ids), $resultSetMapping)
            ->setParameters($queryFactory->getParams());
        return array_column($dbQuery->getResult(), 'id');
    }

    /**
     * @param ResourceEntity[] $fragmentedTrees
     * @param int $rootId
     * @return ResourceEntity[]
     */
    private function filterOneConnectedComponent($fragmentedTrees, $rootId = 0) {
        /** @var ResourceEntity[] $resourcesByIds */
        $resourcesByIds = [];
        foreach ($fragmentedTrees as $resource) {
            $resourcesByIds[$resource->getId()] = $resource;
        }
        /** @var ResourceEntity[] $connectedResources */
        $connectedResources = [$rootId => 'root'];
        /** @var ResourceEntity[] $discardedResources */
        $discardedResources = [];
        foreach ($resourcesByIds as $id => $resource) {
            $this->assignResourceToComponent($id, $resource, $rootId, $resourcesByIds, $connectedResources, $discardedResources);
        }
        unset($connectedResources[$rootId]);
        return $connectedResources;
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    private function assignResourceToComponent($id, $resource, $rootId, $resourcesByIds, &$connectedResources, &$discardedResources) {
        /** @var ResourceEntity[] $line */
        $line = [$id => $resource];
        $nextToCheck = $resource;
        $found = false;
        // look at resource and its ancestors to determine if is below root or not
        while (!$found) {
            if (array_key_exists($nextToCheck->getId(), $connectedResources)
                || (!$nextToCheck->hasParent() && $rootId === 0)
                || $nextToCheck->getParentId() === $rootId) {
                $connectedResources = $connectedResources + $line;
                $found = true;
            } elseif (array_key_exists($nextToCheck->getId(), $discardedResources)
                || (!$nextToCheck->hasParent() && $rootId !== 0)
                || !array_key_exists($nextToCheck->getParentId(), $resourcesByIds)) {
                $discardedResources = $discardedResources + $line;
                $found = true;
            } else {
                /** @var ResourceEntity $nextToCheck */
                $nextToCheck = $resourcesByIds[$nextToCheck->getParentId()];
                $line[$nextToCheck->getId()] = $nextToCheck;
            }
        }
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
        $query = (new TasksQuerySqlFactory($user, $this->workflowRepository))->getSelectQuery();
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceEntity($em);
        $query = $em->createNativeQuery($query, $resultSetMapping);
        return $query->getResult();
    }

    /** @return ResourceEntity[] */
    public function findUsersInGroup(ResourceEntity $userGroup): array {
        $query = ResourceListQuery::builder()->filterByContents(
            ResourceContents::fromArray([SystemMetadata::GROUP_MEMBER => $userGroup->getId()])
        )->build();
        return $this->findByQuery($query)->getResults();
    }

    public function findByDisplayStrategyDependencies(ResourceEntity $resource, array $changedMetadataIds): array {
        $dependentKeys = array_map(
            function ($changedMetadataId) use ($resource) {
                return ResourceDisplayStrategyDependencyMap::createDependencyKey($resource->getId(), $changedMetadataId);
            },
            $changedMetadataIds
        );
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::resourceEntity($em);
        $query = $em->createNativeQuery(
            <<<SQL
        SELECT * FROM (
          SELECT resource.*, jsonb_object_keys(display_strategy_dependencies) dependent_keys
          FROM resource
          WHERE jsonb_typeof(display_strategy_dependencies) = 'object') t
        WHERE dependent_keys IN(:deps)
SQL
            ,
            $resultSetMapping
        );
        $query->setParameter('deps', $dependentKeys);
        return $query->getResult();
    }

    public function markDisplayStrategiesDirty($resources): void {
        if (!is_array($resources)) {
            $resources = [$resources];
        }
        $resourceIds = [];
        $resourceKinds = [];
        foreach ($resources as $resourceOrResourceKind) {
            if ($resourceOrResourceKind instanceof ResourceEntity) {
                $resourceIds[] = $resourceOrResourceKind->getId();
            } elseif ($resourceOrResourceKind instanceof ResourceKind) {
                $resourceKinds[] = $resourceOrResourceKind;
            } else {
                throw new \InvalidArgumentException('Cannot update display strategies of ' . get_class($resourceOrResourceKind));
            }
        }
        if ($resourceIds) {
            $this->createQueryBuilder('r')->update()
                ->set('r.displayStrategiesDirty', 'true')
                ->where('r.id IN(:ids)')
                ->setParameter('ids', $resourceIds)
                ->getQuery()
                ->execute();
        }
        if ($resourceKinds) {
            $this->createQueryBuilder('r')->update()
                ->set('r.displayStrategiesDirty', 'true')
                ->where('r.kind IN(:kinds)')
                ->setParameter('kinds', $resourceKinds)
                ->getQuery()
                ->execute();
        }
    }

    public function hasChildren(ResourceEntity $resource): bool {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::scalar('exists', 'boolean');
        $parentId = SystemMetadata::PARENT;
        $query = $em->createNativeQuery(
            "SELECT exists (SELECT 1 FROM resource r WHERE r.contents->'$parentId'@>:parentId)",
            $resultSetMapping
        );
        $query->setParameter('parentId', '[{"value":' . $resource->getId() . '}]');
        return $query->useResultCache(true, 60)->getSingleScalarResult();
    }
}
