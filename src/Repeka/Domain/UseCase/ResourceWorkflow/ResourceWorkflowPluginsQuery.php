<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\ResourceClassAwareCommand;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceWorkflowPluginsQuery extends ResourceClassAwareCommand implements NonValidatedCommand {
    /** @var ResourceWorkflow */
    private $workflow;

    public function __construct(ResourceWorkflow $workflow) {
        parent::__construct($workflow);
        $this->workflow = $workflow;
    }

    public function getWorkflow(): ResourceWorkflow {
        return $this->workflow;
    }
}
