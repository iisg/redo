<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowSimulateCommandValidator extends ResourceWorkflowUpdateCommandValidator {
    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('places', $this->workflowPlacesDefinitionIsValidRule)
            ->attribute('transitions', $this->workflowTransitionsDefinitionIsValidRule);
    }
}
