<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Repeka\Domain\Validation\Rules\ResourceClassExistsRule;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MetadataCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var IsValidControlRule */
    private $isValidControlRule;
    /** @var ConstraintSetMatchesControlRule */
    private $constraintSetMatchesControlRule;
    /** @var ConstraintArgumentsAreValidRule */
    private $constraintArgumentsAreValidRule;
    /** @var  ResourceClassExistsRule */
    private $resourceClassExistsRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        IsValidControlRule $isValidControlRule,
        ConstraintSetMatchesControlRule $constraintSetMatchesControlRule,
        ConstraintArgumentsAreValidRule $constraintArgumentsAreValidRule,
        ResourceClassExistsRule $resourceClassExistsRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->isValidControlRule = $isValidControlRule;
        $this->constraintSetMatchesControlRule = $constraintSetMatchesControlRule;
        $this->constraintArgumentsAreValidRule = $constraintArgumentsAreValidRule;
        $this->resourceClassExistsRule = $resourceClassExistsRule;
    }

    /**
     * @inheritdoc
     * @param MetadataCreateCommand $command
     */
    public function getValidator(Command $command): Validatable {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('name', Validator::notBlank())
            ->attribute(
                'controlName',
                Validator::callback(
                    function ($control) {
                        return MetadataControl::isValid($control);
                    }
                )
            )
            ->attribute('shownInBrief', Validator::boolType())
            ->attribute('copyToChildResource', Validator::boolType())
            ->attribute('resourceClass', $this->resourceClassExistsRule)
            ->attribute('constraints', $this->constraintSetMatchesControlRule->forControl($command->getControlName()))
            ->attribute('constraints', $this->constraintArgumentsAreValidRule);
    }
}
