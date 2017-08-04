<?php
namespace Repeka\Domain\Exception\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;

class NoSuchTransitionException extends ResourceWorkflowException {
    public function __construct(string $transitionId, ResourceWorkflow $workflow, \Exception $previous = null) {
        parent::__construct('noSuchTransition', $workflow, ['transitionId' => $transitionId], $previous);
    }
}
