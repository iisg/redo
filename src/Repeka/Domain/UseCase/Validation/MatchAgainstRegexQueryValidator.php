<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Validation\CommandAttributesValidator;
use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;
use Respect\Validation\Validatable;
use Respect\Validation\Validator;

class MatchAgainstRegexQueryValidator extends CommandAttributesValidator {
    /** @var RegexConstraint */
    private $regexConstraint;

    public function __construct(RegexConstraint $regexConstraint) {
        $this->regexConstraint = $regexConstraint;
    }

    /**
     * @param MatchAgainstRegexQuery $command
     */
    public function getValidator(Command $command): Validatable {
        return Validator::attribute(
            'regex',
            Validator::callback(
                function ($regex) {
                    return $this->regexConstraint->isConfigValid($regex);
                }
            )
        );
    }
}
