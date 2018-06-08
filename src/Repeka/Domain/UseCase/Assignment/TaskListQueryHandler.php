<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;

class TaskListQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var TransitionPossibilityChecker */
    private $transitionPossibilityChecker;

    public function __construct(ResourceRepository $resourceRepository, TransitionPossibilityChecker $transitionPossibilityChecker) {
        $this->resourceRepository = $resourceRepository;
        $this->transitionPossibilityChecker = $transitionPossibilityChecker;
    }

    public function handle(TaskListQuery $command) {
        $resources = $this->resourceRepository->findAssignedTo($command->getUser());
        $groupedResources = [];
        foreach ($resources as $resource) {
            $transitions = $resource->getWorkflow()->getTransitions($resource);
            foreach ($transitions as $transition) {
                if ($this->transitionPossibilityChecker->isTransitionGuardedByAssignees($resource, $transition)) {
                    if ($this->transitionPossibilityChecker->executorIsAssignee($resource, $transition, $command->getUser())) {
                        $groupedResources[$resource->getResourceClass()][] = $resource;
                        break;
                    }
                }
            }
        }
        return array_values(
            array_map(
                function (array $resources) {
                    $resourceClass = $resources[0]->getResourceClass();
                    return new TasksCollection($resourceClass, $resources);
                },
                $groupedResources
            )
        );
    }
}
