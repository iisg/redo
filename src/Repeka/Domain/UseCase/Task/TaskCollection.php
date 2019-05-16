<?php
namespace Repeka\Domain\UseCase\Task;

use Repeka\Domain\Constants\TaskStatus;
use Repeka\Domain\UseCase\PageResult;

class TaskCollection {
    /** @var string */
    private $resourceClass;
    /** @var TaskStatus */
    private $taskStatus;
    /** @var PageResult[ResourceEntity] */
    private $tasks;

    public function __construct(string $resourceClass = '', $taskStatus = null, PageResult $tasks = null) {
        $this->resourceClass = $resourceClass;
        $this->taskStatus = $taskStatus;
        $this->tasks = $tasks;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    public function getTaskStatus(): TaskStatus {
        return $this->taskStatus;
    }

    public function getTasks(): ?PageResult {
        return $this->tasks;
    }
}
