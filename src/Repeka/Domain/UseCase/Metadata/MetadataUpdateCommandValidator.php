<?php
namespace Repeka\Domain\UseCase\Metadata;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Domain\Validation\Rules\ConstraintSetMatchesControlRule;
use Repeka\Domain\Validation\Rules\ContainsOnlyAvailableLanguagesRule;
use Respect\Validation\Validator;

class MetadataUpdateCommandValidator extends CommandAttributesValidator {
    /** @var ContainsOnlyAvailableLanguagesRule */
    private $containsOnlyAvailableLanguagesRule;
    /** @var ConstraintSetMatchesControlRule */
    private $constraintSetMatchesControlRule;
    /** @var ConstraintArgumentsAreValidRule */
    private $constraintArgumentsAreValidRule;

    public function __construct(
        ContainsOnlyAvailableLanguagesRule $containsOnlyAvailableLanguagesRule,
        ConstraintSetMatchesControlRule $constraintSetMatchesControlRule,
        ConstraintArgumentsAreValidRule $constraintArgumentsAreValidRule
    ) {
        $this->containsOnlyAvailableLanguagesRule = $containsOnlyAvailableLanguagesRule;
        $this->constraintSetMatchesControlRule = $constraintSetMatchesControlRule;
        $this->constraintArgumentsAreValidRule = $constraintArgumentsAreValidRule;
    }

    /**
     * @param MetadataUpdateCommand $command
     */
    public function getValidator(Command $command): Validator {
        return Validator
            ::attribute('metadataId', Validator::intVal()->min(1))
            ->attribute('newLabel', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('newPlaceholder', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('newDescription', $this->containsOnlyAvailableLanguagesRule)
            ->attribute('newShownInBrief', Validator::boolType())
            ->attribute('newConstraints', $this->constraintSetMatchesControlRule->forMetadataId($command->getMetadataId()))
            ->attribute('newConstraints', $this->constraintArgumentsAreValidRule);
    }
}
