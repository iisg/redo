<?php
namespace Repeka\Domain\UseCase\ResourceKind;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class ResourceKindUpdateCommandValidator extends CommandAttributesValidator {
    /** @var ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule */
    private $rkConstraintIsUserIfNecessaryRule;
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ResourceKindConstraintIsUserIfMetadataDeterminesAssigneeRule $rkConstraintIsUserIfNecessaryRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->rkConstraintIsUserIfNecessaryRule = $rkConstraintIsUserIfNecessaryRule;
    }

    /** @inheritdoc */
    public function getValidator(Command $command): Validatable {
        return Validator::allOf(
            Validator::attribute('label', $this->notBlankInAllLanguagesRule)
                ->attribute('resourceKindId', Validator::intVal()->min(1))
                ->attribute('metadataList', Validator::each(Validator::callback([$this, 'validateMetadata'])
                    ->setTemplate('relationship must be constrained to users because metadata determines assignee')
                    ->setName('metadataList')))
        );
    }

    public function validateMetadata(array $metadata) {
        $constraintsValidator = $this->rkConstraintIsUserIfNecessaryRule->forMetadataId($metadata['baseId']);
        return Validator::key('constraints', $constraintsValidator, false)->validate($metadata);
    }
}
