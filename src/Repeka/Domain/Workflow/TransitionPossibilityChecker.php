<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceContents;
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
        ResourceContents $newContents,
        ResourceWorkflowTransition $transition,
        User $executor
    ): TransitionPossibilityCheckResult {
        return new TransitionPossibilityCheckResult(
            $this->determineMissingMetadataIds($resource, $newContents, $transition),
            $this->executorIsMissingRequiredRole($transition, $executor),
            $this->executorIsNotAssignee($resource, $transition, $executor)
        );
    }

    private function determineMissingMetadataIds(
        ResourceEntity $resource,
        ResourceContents $resourceContents,
        ResourceWorkflowTransition $transition
    ): array {
        if (!$resource->hasWorkflow()) {
            return [];
        }
        $workflow = $resource->getWorkflow();
        $targetPlaces = EntityUtils::getByIds($transition->getToIds(), $workflow->getPlaces());
        $missingMetadataIds = [];
        foreach ($targetPlaces as $targetPlace) {
            /** @var ResourceWorkflowPlace $targetPlace */
            $metadataIdsMissingForPlace = $targetPlace->getMissingRequiredMetadataIds($resourceContents);
            $missingMetadataIds = array_merge($missingMetadataIds, $metadataIdsMissingForPlace);
        }
        $resourceKindMetadataIds = $resource->getKind()->getMetadataIds();
        $missingMetadataIds = array_intersect($missingMetadataIds, $resourceKindMetadataIds);
        return array_values(array_unique($missingMetadataIds));
    }

    private function executorIsMissingRequiredRole(ResourceWorkflowTransition $transition, User $executor): bool {
        return SystemTransition::isValid($transition->getId()) ? false : !$transition->userHasRoleRequiredToApply($executor);
    }

    private function executorIsNotAssignee(ResourceEntity $resource, ResourceWorkflowTransition $transition, User $executor): bool {
        if (!$resource->hasWorkflow()) {
            return false;
        }
        $assigneeMetadataIds = $this->getAssigneeMetadataIds($resource->getWorkflow(), $transition);
        $assigneeMetadataIds = array_intersect($assigneeMetadataIds, $resource->getKind()->getMetadataIds());

        if (empty($assigneeMetadataIds)) {
            return false;  // no metadata determines assignees, so everyone can perform transition
        }
        $assigneeUserIds = $this->extractAssigneeIds($resource, $assigneeMetadataIds);
        $executorUserIds = EntityUtils::mapToIds($this->userRepository->findUserGroups($executor));
        $executorUserIds[] = $executor->getUserData()->getId();

        return count(array_intersect($assigneeUserIds, $executorUserIds)) == 0;
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
        $resourceKindMetadataIds = $resource->getKind()->getMetadataIds();
        $assigneeMetadataIds = array_values(array_intersect($assigneeMetadataIds, $resourceKindMetadataIds));
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
