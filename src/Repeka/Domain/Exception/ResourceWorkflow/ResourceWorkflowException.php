<?php
namespace Repeka\Domain\Exception\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\DomainException;

class ResourceWorkflowException extends DomainException {
    public function __construct(string $message, ResourceWorkflow $workflow, \Exception $previous = null) {
        parent::__construct("Exception in workflow #{$workflow->getId()}: $message", $previous);
        $this->setCode(409);
    }
}
