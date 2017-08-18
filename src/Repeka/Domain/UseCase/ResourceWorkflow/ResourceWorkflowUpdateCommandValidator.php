<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowUpdateCommandValidator extends CommandAttributesValidator {
    /** @var EntityExistsRule */
    private $entityExistsRule;
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;

    public function __construct(EntityExistsRule $entityExistsRule, NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule) {
        $this->entityExistsRule = $entityExistsRule;
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('workflow', Validator::instance(ResourceWorkflow::class)->callback(function (ResourceWorkflow $workflow) {
                return $workflow->getId() > 0;
            }))
            ->attribute('name', $this->notBlankInAllLanguagesRule)
            ->attribute('places', $this->placesValidator())
            ->attribute('transitions', $this->transitionsValidator());
    }

    protected function placesValidator(): Validator {
        $metadataExistsRule = $this->entityExistsRule->forEntityType(Metadata::class);
        return Validator::arrayType()->each(Validator::oneOf(
            Validator::instance(ResourceWorkflowPlace::class),
            Validator::arrayType()->keySet(
                Validator::key('label', Validator::arrayType()),
                Validator::key('id', Validator::stringType(), false),
                Validator::key('requiredMetadataIds', Validator::arrayType()->each($metadataExistsRule), false)
            )
        ));
    }

    protected function transitionsValidator(): Validator {
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
