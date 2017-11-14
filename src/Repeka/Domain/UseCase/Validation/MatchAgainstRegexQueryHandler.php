<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;

class MatchAgainstRegexQueryHandler {
    /** @var RegexConstraint */
    private $regexConstraint;

    public function __construct(RegexConstraint $regexConstraint) {
        $this->regexConstraint = $regexConstraint;
    }

    public function handle(MatchAgainstRegexQuery $query): bool {
        return $this->regexConstraint->isValueValid($query->getRegex(), $query->getValues());
    }
}
