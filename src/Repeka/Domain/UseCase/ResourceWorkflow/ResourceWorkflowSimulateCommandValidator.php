<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Respect\Validation\Validator;

class ResourceWorkflowSimulateCommandValidator extends ResourceWorkflowUpdateCommandValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('places', $this->placesValidator()->length(1))
            ->attribute('transitions', $this->transitionsValidator());
    }
}
