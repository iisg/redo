<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Repeka\Domain\Validation\Rules\IsValidControlRule;
use Repeka\Domain\Validation\Rules\NotBlankInAllLanguagesRule;
use Respect\Validation\Validator;

class MetadataCreateCommandValidator extends CommandAttributesValidator {
    /** @var NotBlankInAllLanguagesRule */
    private $notBlankInAllLanguagesRule;
    /** @var ContainsOnlyAvailableLanguagesRule */
    private $containsOnlyAvailableLanguagesRule;
    /** @var IsValidControlRule */
    private $isValidControlRule;
    /** @var ConstraintSetMatchesControlRule */
    private $constraintSetMatchesControlRule;
    /** @var ConstraintArgumentsAreValidRule */
    private $constraintArgumentsAreValidRule;

    public function __construct(
        NotBlankInAllLanguagesRule $notBlankInAllLanguagesRule,
        ContainsOnlyAvailableLanguagesRule $containsOnlyAvailableLanguagesRule,
        IsValidControlRule $isValidControlRule,
        ConstraintSetMatchesControlRule $constraintSetMatchesControlRule,
        ConstraintArgumentsAreValidRule $constraintArgumentsAreValidRule
    ) {
        $this->notBlankInAllLanguagesRule = $notBlankInAllLanguagesRule;
        $this->containsOnlyAvailableLanguagesRule = $containsOnlyAvailableLanguagesRule;
        $this->isValidControlRule = $isValidControlRule;
        $this->constraintSetMatchesControlRule = $constraintSetMatchesControlRule;
        $this->constraintArgumentsAreValidRule = $constraintArgumentsAreValidRule;
    }

    /**
     * @inheritdoc
     * @param MetadataCreateCommand $command
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('label', $this->notBlankInAllLanguagesRule)
            ->attribute('name', Validator::notBlank())
            ->attribute('placeholder', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('description', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('control', $this->isValidControlRule)
            ->attribute('shownInBrief', Validator::boolType())
            ->attribute('constraints', $this->constraintSetMatchesControlRule->forControl($command->getControl()))
            ->attribute('constraints', $this->constraintArgumentsAreValidRule);
    }
}
