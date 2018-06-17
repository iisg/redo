<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Entity\ResourceEntity;

class TasksCollection {
    /** @var string */
    private $resourceClass;
    /** @var ResourceEntity[] */
    private $myTasks;
    /** @var ResourceEntity[] */
    private $possibleTasks;

    public function __construct(string $resourceClass = '', array $myTasks = [], array $possibleTasks = []) {
        $this->resourceClass = $resourceClass;
        $this->myTasks = $myTasks;
        $this->possibleTasks = $possibleTasks;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    /** @return ResourceEntity[] */
    public function getMyTasks(): array {
        return $this->myTasks;
    }

    /** @return ResourceEntity[] */
    public function getPossibleTasks(): array {
        return $this->possibleTasks;
    }
}
