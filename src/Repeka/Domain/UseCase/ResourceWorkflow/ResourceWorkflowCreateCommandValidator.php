<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceWorkflowPlace;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\EntityExistsRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceWorkflowCreateCommandValidator extends CommandAttributesValidator {
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
            ::attribute('name', $this->notBlankInAllLanguagesRule)
            ->attribute('places', $this->placesValidator())
            ->attribute('transitions', $this->transitionsValidator());
    }

    protected function placesValidator(): Validator {
        $metadataExistsRule = $this->entityExistsRule->forEntityType(Metadata::class);
        return Validator::arrayType()->length(1)->each(Validator::oneOf(
            Validator::instance(ResourceWorkflowPlace::class),
            Validator::arrayType()->keySet(
                Validator::key('label', Validator::arrayType()),
                Validator::key('id', Validator::stringType(), false),
                Validator::key('requiredMetadataIds', Validator::arrayType()->each($metadataExistsRule), false),
                Validator::key('lockedMetadataIds', Validator::arrayType()->each($metadataExistsRule), false),
                Validator::key('assigneeMetadataIds', Validator::arrayType()->each($metadataExistsRule), false)
            )->callback([$this, 'noCommonValuesBetweenRequirements'])
        ));
    }

    public function noCommonValuesBetweenRequirements($place): bool {
        $merged = ($place instanceof ResourceWorkflowPlace)
            ? array_merge($place->getRequiredMetadataIds(), $place->getLockedMetadataIds(), $place->getAssigneeMetadataIds())
            : array_merge($place['requiredMetadataIds'] ?? [], $place['lockedMetadataIds'] ?? [], $place['assigneeMetadataIds'] ?? []);
        $allCount = count($merged);
        $uniqueCount = count(array_unique($merged));
        return $allCount == $uniqueCount;
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
