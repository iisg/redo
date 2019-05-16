<?php
namespace Repeka\Domain\UseCase\Task;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Constants\SystemRole;
use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\Utils\EntityUtils;

class TasksFinder {
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;
    /** @var ResourceKindRepository */
    private $resourceKindRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ResourceWorkflowRepository $workflowRepository,
        ResourceKindRepository $resourceKindRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->workflowRepository = $workflowRepository;
        $this->resourceKindRepository = $resourceKindRepository;
        $this->entityManager = $entityManager;
    }

    /** @return int[] */
    public function getTasksIdsForUserOnly(User $user, string $resourceClass): array {
        return $this->getTasksIds([$user->getUserResourceId()], $resourceClass);
    }

    /** @return int[] */
    public function getTasksIdsForUserGroupsOnly(User $user, string $resourceClass): array {
        return $this->getTasksIds($user->getUserGroupsIds(), $resourceClass);
    }

    /** @return int[] */
    public function getTasksIdsForUserAndTheirGroups(User $user, string $resourceClass): array {
        return $this->getTasksIds($user->getGroupIdsWithUserId(), $resourceClass);
    }

    private function getTasksIds(array $assigneeIds, string $resourceClass): array {
        $querySql = $this->buildTasksQuery($assigneeIds, $resourceClass);
        if ($querySql) {
            $resultSetMapping = ResultSetMappings::scalar('id');
            $query = $this->entityManager->createNativeQuery($querySql, $resultSetMapping);
            return EntityUtils::mapToIds($query->getResult());
        } else {
            return [];
        }
    }

    /** @SuppressWarnings("PHPMD.CyclomaticComplexity") */
    private function buildTasksQuery(array $assigneeIds, string $resourceClass): ?string {
        $workflows = $this->workflowRepository->findAllByResourceClass($resourceClass);
        $conditions = [];
        foreach ($workflows as $workflow) {
            $workflowConditions = [];
            foreach ($workflow->getPlaces() as $place) {
                $assigneeMetadataIds = $place->restrictingMetadataIds()->assignees()->autoAssign()->get();
                if ($assigneeMetadataIds) {
                    $transitions = $workflow->getTransitionsToPlace($place);
                    foreach ($assigneeMetadataIds as $assigneeMetadataId) {
                        foreach ($transitions as $transition) {
                            foreach ($transition->getFromIds() as $placeId) {
                                foreach ($assigneeIds as $groupId) {
                                    $workflowConditions[] = $this->assigneeMetadataCondition($placeId, $assigneeMetadataId, $groupId);
                                }
                            }
                        }
                    }
                }
                if ($workflowConditions) {
                    $resourceKindQuery = ResourceKindListQuery::builder()->filterByWorkflowId($workflow->getId())->build();
                    $resourceKindIds = EntityUtils::mapToIds($this->resourceKindRepository->findByQuery($resourceKindQuery));
                    if ($resourceKindIds) {
                        $conditions[] = sprintf('(kind_id IN (%s) AND (', implode(',', $resourceKindIds))
                            . implode(') OR (', $workflowConditions) . '))';
                    }
                }
            }
        }
        if ($conditions) {
            $where = '((' . implode(') OR (', $conditions) . '))';
            return sprintf('SELECT id FROM resource WHERE %s', $where);
        } else {
            return null;
        }
    }

    private function assigneeMetadataCondition(string $placeId, int $assigneeMetadataId, int $principalId): string {
        return sprintf(
            "marking->'%s' IS NOT NULL AND jsonb_contains(contents->'%d', '[{\"value\": %d}]'::JSONB) = TRUE",
            $placeId,
            $assigneeMetadataId,
            $principalId
        );
    }

    /**
     * @param User $user
     * @return array [ resourceClass => [own => [1,2,3], possible => [4,5,6] ]
     */
    public function getAllTasks(User $user): array {
        $groupedTasksIds = [];
        foreach ($user->resourceClassesInWhichUserHasRole(SystemRole::OPERATOR()) as $resourceClass) {
            $groupedTasksIds[$resourceClass] = [
                TaskStatus::OWN => $this->getTasksIdsForUserOnly($user, $resourceClass),
                TaskStatus::POSSIBLE => $this->getTasksIdsForUserGroupsOnly($user, $resourceClass),
            ];
        }
        return $groupedTasksIds;
    }
}
