<?php
namespace Repeka\Domain\Workflow;

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
            !$this->executorIsAssignee($resource, $transition, $executor)
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

    private function executorIsAssignee(ResourceEntity $resource, ResourceWorkflowTransition $transition, User $executor): bool {
        if (!$resource->hasWorkflow()) {
            return true;
        }
        $assigneeMetadataIds = $this->getAssigneeMetadataIds($resource->getWorkflow(), $transition);
        $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($resource->getWorkflow(), $transition);
        $assigneeMetadataIds = array_intersect($assigneeMetadataIds, $resource->getKind()->getMetadataIds());
        $autoAssignMetadataIds = array_intersect($autoAssignMetadataIds, $resource->getKind()->getMetadataIds());
        if (empty($assigneeMetadataIds) && empty($autoAssignMetadataIds)) {
            return true;  // no metadata determines assignees, so everyone can perform transition
        }
        $assigneeUserIds = $this->extractAssigneeIds($resource, $assigneeMetadataIds);
        $autoAssignUserIds = $this->extractAssigneeIds($resource, $autoAssignMetadataIds);
        $executorUserIds = $executor->getUserGroupsIds();
        $executorUserIds[] = $executor->getUserData()->getId();

        $executorIsAssignee = count(array_intersect($assigneeUserIds, $executorUserIds)) != 0;
        $executorIsAutoAssigned = count(array_intersect($autoAssignUserIds, $executorUserIds)) != 0;
        $noAssigneeMetadata = empty($assigneeMetadataIds);
        $noOneIsAutoAssigned = empty($autoAssignUserIds);
        $executorCanBeAutoAssigned = $noAssigneeMetadata && $noOneIsAutoAssigned;
        return $executorIsAssignee || $executorIsAutoAssigned || $executorCanBeAutoAssigned;
    }

    /**
     * Gets assignee and auto-assignee metadata list for each of transition tos, merges these lists, removes duplicates and returns only IDs
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

    public function getAutoAssignMetadataIds(ResourceWorkflow $workflow, ResourceWorkflowTransition $transition): array {
        /** @var ResourceWorkflowPlace[] $transitionTos */
        $transitionTos = EntityUtils::getByIds($transition->getToIds(), $workflow->getPlaces());
        $autoAssignMetadataIds = [];
        foreach ($transitionTos as $place) {
            $autoAssignMetadataIds = array_merge($autoAssignMetadataIds, $place->restrictingMetadataIds()->autoAssign()->get());
        }
        $autoAssignMetadataIds = array_unique($autoAssignMetadataIds);
        return $autoAssignMetadataIds;
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
