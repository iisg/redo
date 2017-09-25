<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Repository\AssignmentFinder;

class AssignmentListQueryHandler {
    /** @var AssignmentFinder */
    private $assignmentFinder;

    public function __construct(AssignmentFinder $assignmentFinder) {
        $this->assignmentFinder = $assignmentFinder;
    }

    public function handle(AssignmentListQuery $command) {
        return $this->assignmentFinder->findAssignedResources($command->getUser());
    }
}
