<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;

class ResourceWorkflowListQuery extends Command {
    private $resourceClass;

    public function __construct(string $resourceClass) {
        $this->resourceClass = $resourceClass;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
