<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Respect\Validation\Validator;

class ResourceWorkflowUpdateCommandValidator extends CommandAttributesValidator {
    /**
     * @inheritdoc
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('workflow', Validator::instance(ResourceWorkflow::class)->callback(function (ResourceWorkflow $workflow) {
                return $workflow->getId() > 0;
            }))
            ->attribute('places', $this->placesValidator())
            ->attribute('transitions', $this->transitionsValidator());
    }

    protected function placesValidator():Validator {
        return Validator::arrayType()->each(Validator::oneOf(
            Validator::instance(ResourceWorkflowPlace::class),
            Validator::arrayType()->keySet(
                Validator::key('label', Validator::arrayType()),
                Validator::key('id', Validator::stringType(), false)
            )
        ));
    }

    protected function transitionsValidator():Validator {
        return Validator::arrayType()->each(Validator::oneOf(
            Validator::instance(ResourceWorkflowTransition::class),
            Validator::arrayType()->keySet(
                Validator::key('label', Validator::arrayType()),
                Validator::key('froms', Validator::arrayType()),
                Validator::key('tos', Validator::arrayType()),
                Validator::key('permittedRoleIds', Validator::arrayType(), false),
                Validator::key('id', Validator::stringType(), false)
            )
        ));
    }
}
