<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Utils\EntityUtils;

class TransitionAssigneeChecker {
    public function isTransitionGuardedByAssignees(ResourceEntity $resource, ResourceWorkflowTransition $transition) {
        if ($resource->hasWorkflow()) {
            $assigneeMetadataIds = $this->getAssigneeMetadataIds($resource, $transition);
            $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($resource, $transition);
            return $assigneeMetadataIds || $autoAssignMetadataIds;
        }
        return false;
    }

    public function canApplyTransition(ResourceEntity $resource, ResourceWorkflowTransition $transition, User $executor): bool {
        if (!$this->isTransitionGuardedByAssignees($resource, $transition)) {
            return true;
        }
        $assigneeMetadataIds = $this->getAssigneeMetadataIds($resource, $transition);
        $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($resource, $transition);
        $assigneeUserIds = $this->extractAssigneeIds($resource, $assigneeMetadataIds);
        $autoAssignUserIds = $this->extractAssigneeIds($resource, $autoAssignMetadataIds);
        $executorIsAssignee = $executor->belongsToAnyOfGivenUserGroupsIds($assigneeUserIds);
        $executorIsAutoAssigned = $executor->belongsToAnyOfGivenUserGroupsIds($autoAssignUserIds);
        $noAssigneeMetadata = empty($assigneeMetadataIds);
        $noOneIsAutoAssigned = empty($autoAssignUserIds);
        $executorCanBeAutoAssigned = $noAssigneeMetadata && $noOneIsAutoAssigned;
        return $executorIsAssignee || $executorIsAutoAssigned || $executorCanBeAutoAssigned;
    }

    public function getUserIdsAssignedToTransition(ResourceEntity $resource, ResourceWorkflowTransition $transition) {
        if ($this->isTransitionGuardedByAssignees($resource, $transition)) {
            $assigneeMetadataIds = $this->getAssigneeMetadataIds($resource, $transition);
            $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($resource, $transition);
            $assigneeUserIds = $this->extractAssigneeIds($resource, $assigneeMetadataIds);
            $autoAssignUserIds = $this->extractAssigneeIds($resource, $autoAssignMetadataIds);
            return array_values(array_unique(array_merge($assigneeUserIds, $autoAssignUserIds)));
        }
        return [];
    }

    /**
     * Gets assignee and auto-assignee metadata list for each of transition tos, merges these lists, removes duplicates and returns only IDs
     * @return int[]
     */
    public function getAssigneeMetadataIds(ResourceEntity $resource, ResourceWorkflowTransition $transition): array {
        return $this->getMetadataIdsWithBuilder($resource, $transition, 'assignees');
    }

    public function getAutoAssignMetadataIds(ResourceEntity $resource, ResourceWorkflowTransition $transition): array {
        return $this->getMetadataIdsWithBuilder($resource, $transition, 'autoAssign');
    }

    private function getMetadataIdsWithBuilder(ResourceEntity $resource, ResourceWorkflowTransition $transition, string $method): array {
        /** @var ResourceWorkflowPlace[] $transitionTos */
        $transitionTos = EntityUtils::getByIds($transition->getToIds(), $resource->getWorkflow()->getPlaces());
        $metadataIds = [];
        foreach ($transitionTos as $place) {
            $metadataIds = array_merge(
                $metadataIds,
                $place->restrictingMetadataIds()->$method()->existingInResourceKind($resource->getKind())->get()
            );
        }
        $metadataIds = array_values(array_unique($metadataIds));
        return $metadataIds;
    }

    private function extractAssigneeIds(ResourceEntity $resource, array $assigneeMetadataIds): array {
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
