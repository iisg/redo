<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Repository\ResourceRepository;

class TaskListQueryHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(TaskListQuery $command) {
        return $this->resourceRepository->findAssignedTo($command->getUser());
    }
}
