<?php
namespace Repeka\Domain\Exception\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\DomainException;

class ResourceWorkflowException extends DomainException {
    public function __construct(string $errorMessageId, ResourceWorkflow $workflow, array $params, \Exception $previous = null) {
        parent::__construct($errorMessageId, 409, array_merge(['workflowId' => $workflow->getId()], $params), $previous);
    }
}
