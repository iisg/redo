<?php
namespace Repeka\Domain\UseCase\Assignment;

use Repeka\Domain\Entity\ResourceEntity;

class TasksCollection {
    /** @var string */
    private $resourceClass;
    /** @var ResourceEntity[] */
    private $myTasks;

    public function __construct(string $resourceClass = '', array $resources = []) {
        $this->resourceClass = $resourceClass;
        $this->myTasks = $resources;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }

    /** @return ResourceEntity[] */
    public function getMyTasks(): array {
        return $this->myTasks;
    }
}
