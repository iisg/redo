<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceWorkflowUpdateCommand extends ResourceWorkflowCreateCommand {
    /** @var ResourceWorkflow */
    private $workflow;

    public function __construct(ResourceWorkflow $workflow, array $name, array $places, array $transitions, $diagram, $thumbnail) {
        parent::__construct($name, $places, $transitions, $diagram, $thumbnail);
        $this->workflow = $workflow;
    }

    public function getWorkflow(): ResourceWorkflow {
        return $this->workflow;
    }
}
