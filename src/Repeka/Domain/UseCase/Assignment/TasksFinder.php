<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Workflow\TransitionAssigneeChecker;

class TasksFinder {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var TransitionAssigneeChecker */
    private $transitionAssigneeChecker;

    public function __construct(ResourceRepository $resourceRepository, TransitionAssigneeChecker $transitionAssigneeChecker) {
        $this->resourceRepository = $resourceRepository;
        $this->transitionAssigneeChecker = $transitionAssigneeChecker;
    }

    public function getAllTasks(User $user) {
        $resources = $this->resourceRepository->findAssignedTo($user);
        $groupedResources = [];
        foreach ($resources as $resource) {
            $transitions = $resource->getWorkflow()->getTransitions($resource);
            $taskStatus = null;
            foreach ($transitions as $transition) {
                $assignedUserIds = $this->transitionAssigneeChecker->getUserIdsAssignedToTransition($resource, $transition);
                if (count($assignedUserIds) > 0) {
                    if ($this->transitionAssigneeChecker->canApplyTransition($resource, $transition, $user)) {
                        $myOrPossible = $assignedUserIds == [$user->getUserData()->getId()] ? TaskStatus::OWN : TaskStatus::POSSIBLE;
                        if ($taskStatus != TaskStatus::OWN) {
                            $taskStatus = $myOrPossible;
                        }
                    }
                }
            }
            if ($taskStatus) {
                $groupedResources[$resource->getResourceClass()][$taskStatus][] = $resource;
            }
        }
        return $groupedResources;
    }
}
