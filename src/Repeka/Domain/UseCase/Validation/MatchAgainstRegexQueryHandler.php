<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Validation\MetadataConstraints\RegexConstraint;

class MatchAgainstRegexQueryHandler {
    /** @var RegexConstraint */
    private $regexConstraint;

    public function __construct(RegexConstraint $regexConstraint) {
        $this->regexConstraint = $regexConstraint;
    }

    public function handle(MatchAgainstRegexQuery $query) {
        $dummyMetadata = Metadata::create('books', MetadataControl::TEXT(), 'dummy', []);
        $this->regexConstraint->validateSingle($dummyMetadata, $query->getRegex(), $query->getValue());
    }
}
