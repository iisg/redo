<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceWorkflowPluginsQuery extends AbstractCommand implements NonValidatedCommand {
    /** @var ResourceWorkflow */
    private $workflow;

    public function __construct(ResourceWorkflow $workflow) {
        $this->workflow = $workflow;
    }

    public function getWorkflow(): ResourceWorkflow {
        return $this->workflow;
    }
}
