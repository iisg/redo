<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceWorkflowDeleteCommand extends ResourceClassAwareCommand {
    /** @var ResourceWorkflow */
    private $resourceWorkflow;

    public function __construct(ResourceWorkflow $resourceWorkflow) {
        parent::__construct($resourceWorkflow);
        $this->resourceWorkflow = $resourceWorkflow;
    }

    public function getResourceWorkflow(): ResourceWorkflow {
        return $this->resourceWorkflow;
    }
}
