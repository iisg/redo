<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\AbstractCommand;

class ResourceWorkflowListQuery extends AbstractCommand {
    private $resourceClass;

    public function __construct(string $resourceClass) {
        $this->resourceClass = $resourceClass;
    }

    public function getResourceClass(): string {
        return $this->resourceClass;
    }
}
