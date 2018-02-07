<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceWorkflowDeleteCommand extends AbstractCommand {
    /** @var ResourceWorkflow */
    private $resourceWorkflow;

    public function __construct(ResourceWorkflow $resourceWorkflow) {
        $this->resourceWorkflow = $resourceWorkflow;
    }

    public function getResourceWorkflow(): ResourceWorkflow {
        return $this->resourceWorkflow;
    }
}
