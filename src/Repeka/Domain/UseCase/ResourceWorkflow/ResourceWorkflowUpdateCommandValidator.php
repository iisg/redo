<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowUpdateCommandValidator extends ResourceWorkflowCreateCommandValidator {
    public function __construct(EntityExistsRule $entityExistsRule, NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule) {
        parent::__construct($entityExistsRule, $notBlankInAllLanguagesRule);
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator::allOf(
            Validator::attribute(
                'workflow',
                Validator::instance(ResourceWorkflow::class)
                    ->callback(function (ResourceWorkflow $workflow) {
                        return $workflow->getId() > 0;
                    })
            ),
            parent::getValidator($command)
        );
    }
}
