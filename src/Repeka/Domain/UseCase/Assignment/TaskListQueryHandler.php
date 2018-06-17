<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Workflow\TransitionAssigneeChecker;

class TaskListQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var TransitionAssigneeChecker */
    private $transitionAssigneeChecker;

    public function __construct(ResourceRepository $resourceRepository, TransitionAssigneeChecker $transitionAssigneeChecker) {
        $this->resourceRepository = $resourceRepository;
        $this->transitionAssigneeChecker = $transitionAssigneeChecker;
    }

    public function handle(TaskListQuery $command) {
        $resources = $this->resourceRepository->findAssignedTo($command->getUser());
        $groupedResources = [];
        foreach ($resources as $resource) {
            $transitions = $resource->getWorkflow()->getTransitions($resource);
            $taskStatus = null;
            foreach ($transitions as $transition) {
                $assignedUserIds = $this->transitionAssigneeChecker->getUserIdsAssignedToTransition($resource, $transition);
                if (count($assignedUserIds) > 0) {
                    if ($this->transitionAssigneeChecker->canApplyTransition($resource, $transition, $command->getUser())) {
                        $myOrPossible = $assignedUserIds == [$command->getUser()->getUserData()->getId()] ? 'my' : 'possible';
                        if ($taskStatus != 'my') {
                            $taskStatus = $myOrPossible;
                        }
                    }
                }
            }
            if ($taskStatus) {
                $groupedResources[$resource->getResourceClass()][$taskStatus][] = $resource;
            }
        }
        return array_values(
            array_map(
                function (array $resources, string $resourceClass) {
                    return new TasksCollection($resourceClass, $resources['my'] ?? [], $resources['possible'] ?? []);
                },
                $groupedResources,
                array_keys($groupedResources)
            )
        );
    }
}
