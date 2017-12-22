<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Cqrs\Command;

class MatchAgainstRegexQuery extends Command {
    /** @var string */
    private $regex;
    /** @var string */
    private $value;

    /**
     * @param string[] $values
     */
    public function __construct(string $regex, string $value) {
        $this->regex = $regex;
        $this->value = $value;
    }

    public function getRegex(): string {
        return $this->regex;
    }

    public function getValue(): string {
        return $this->value;
    }

    public static function fromArray($array): MatchAgainstRegexQuery {
        return new MatchAgainstRegexQuery($array['regex'], $array['value'] ?? '');
    }
}
