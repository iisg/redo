<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class TasksQuerySqlFactory {

    /** @var ResourceWorkflowRepository */
    private $workflowRepository;

    /** @var User */
    protected $user;

    protected $whereAlternatives = [];

    public function __construct(User $user, ResourceWorkflowRepository $workflowRepository) {
        $this->user = $user;
        $this->workflowRepository = $workflowRepository;
        $this->build();
    }

    private function build() {
        $workflows = $this->workflowRepository->findAll();
        $conditions = [];
        $groupsIds = $this->user->getGroupIdsWithUserId();
        foreach ($workflows as $workflow) {
            $resourceClass = $workflow->getResourceClass();
            $places = $workflow->getPlaces();
            foreach ($places as $place) {
                $assigneeMetadataIds = $place->restrictingMetadataIds()->assignees()->autoAssign()->get();
                if ($assigneeMetadataIds) {
                    $transitions = $workflow->getTransitionsToPlace($place);
                    foreach ($assigneeMetadataIds as $assigneeMetadataId) {
                        foreach ($transitions as $transition) {
                            foreach ($transition->getFromIds() as $placeId) {
                                foreach ($groupsIds as $groupId) {
                                    $conditions[] =
                                        $this->assigneeMetadataCondition($resourceClass, $placeId, $assigneeMetadataId, $groupId);
                                }
                            }
                        }
                    }
                }
            }
        }
        $this->whereAlternatives = $conditions ?: ['1=0']; // no conditions - no tasks
    }

    private function assigneeMetadataCondition(string $resourceClass, string $placeId, int $assigneeMetadataId, int $principalId): string {
        return sprintf(
            "resource_class = '%s' AND marking->'%s' IS NOT NULL AND jsonb_contains(contents->'%d', '[{\"value\": %d}]'::JSONB) = TRUE",
            $resourceClass,
            $placeId,
            $assigneeMetadataId,
            $principalId
        );
    }

    public function getSelectQuery() {
        $where = '(' . implode(') OR (', $this->whereAlternatives) . ')';
        return sprintf('SELECT * FROM resource WHERE %s', $where);
    }
}
