<?php
namespace Repeka\Domain\Exception\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;

class NoSuchTransitionException extends ResourceWorkflowException {
    public function __construct(string $transitionId, ResourceWorkflow $workflow, \Exception $previous = null) {
        parent::__construct("Cannot find transition: $transitionId", $workflow, $previous);
    }
}
