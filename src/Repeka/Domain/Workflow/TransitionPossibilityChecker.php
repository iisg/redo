<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Utils\EntityUtils;

class TransitionPossibilityChecker {
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository) {
        $this->userRepository = $userRepository;
    }

    public function check(
        ResourceEntity $resource,
        ResourceWorkflowTransition $transition,
        User $executor
    ): TransitionPossibilityCheckResult {
        return new TransitionPossibilityCheckResult(
            $this->determineMissingMetadataIds($resource, $transition),
            $this->executorIsMissingRequiredRole($transition, $executor),
            $this->executorIsNotAssignee($resource, $transition, $executor)
        );
    }

    private function determineMissingMetadataIds(ResourceEntity $resource, ResourceWorkflowTransition $transition): array {
        $workflow = $resource->getWorkflow();
        $targetPlaces = EntityUtils::getByIds($transition->getToIds(), $workflow->getPlaces());
        $missingMetadataIds = [];
        foreach ($targetPlaces as $targetPlace) {
            /** @var ResourceWorkflowPlace $targetPlace */
            $metadataIdsMissingForPlace = $targetPlace->getMissingRequiredMetadataIds($resource);
            $missingMetadataIds = array_merge($missingMetadataIds, $metadataIdsMissingForPlace);
        }
        return array_unique($missingMetadataIds);
    }

    private function executorIsMissingRequiredRole(ResourceWorkflowTransition $transition, User $executor): bool {
        return !$transition->userHasRoleRequiredToApply($executor);
    }

    private function executorIsNotAssignee(ResourceEntity $resource, ResourceWorkflowTransition $transition, User $executor): bool {
        $assigneeMetadataIds = $this->getAssigneeMetadataIds($resource->getWorkflow(), $transition);
        if (empty($assigneeMetadataIds)) {
            return false;  // no metadata determines assignees, so everyone can perform transition
        }
        $assigneeUserIds = $this->extractAssigneeIds($resource, $assigneeMetadataIds);
        $userGroupsIds = EntityUtils::mapToIds($this->userRepository->findUserGroups($executor));
        $userGroupsIds[] = $executor->getUserData()->getId();
        return count(array_intersect($assigneeUserIds, $userGroupsIds)) == 0;
    }

    /**
     * Gets assignee metadata list for each of transition tos, merges these lists, removes duplicates and returns only IDs
     * @return int[]
     */
    public function getAssigneeMetadataIds(ResourceWorkflow $workflow, ResourceWorkflowTransition $transition): array {
        /** @var ResourceWorkflowPlace[] $transitionTos */
        $transitionTos = EntityUtils::getByIds($transition->getToIds(), $workflow->getPlaces());
        $assigneeMetadataIds = [];
        foreach ($transitionTos as $place) {
            $assigneeMetadataIds = array_merge($assigneeMetadataIds, $place->restrictingMetadataIds()->assignees()->get());
        }
        $assigneeMetadataIds = array_unique($assigneeMetadataIds);
        return $assigneeMetadataIds;
    }

    private function extractAssigneeIds(ResourceEntity $resource, array $assigneeMetadataIds): array {
        $resourceKind = $resource->getKind();
        $assigneeMetadataIds = array_intersect($assigneeMetadataIds, $resourceKind->getMetadataIds());
        $assigneeUserIdArrays = array_map(
            function (int $metadataId) use ($resource) {
                return $resource->getContents()[$metadataId] ?? [];
            },
            $assigneeMetadataIds
        );
        $assigneeUserIdArrays[] = [];  // ensure it's not empty or array_merge will fail
        $assigneeUserIds = call_user_func_array('array_merge', $assigneeUserIdArrays);
        $assigneeUserIds = array_column($assigneeUserIds, 'value');
        return $assigneeUserIds;
    }
}
