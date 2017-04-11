<?php
namespace Repeka\Domain\Exception\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;

class CannotApplyTransitionException extends ResourceWorkflowException {
    public function __construct(string $transitionId, ResourceWorkflow $workflow, \Exception $previous = null) {
        parent::__construct("Cannot apply transition: $transitionId", $workflow, $previous);
    }
}
