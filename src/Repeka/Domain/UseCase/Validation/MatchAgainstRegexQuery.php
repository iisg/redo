<?php
namespace Repeka\Domain\UseCase\Validation;

use Repeka\Domain\Cqrs\Command;

class MatchAgainstRegexQuery extends Command {
    /** @var string */
    private $regex;
    /** @var string[] */
    private $values;

    /**
     * @param string[] $values
     */
    public function __construct(string $regex, array $values) {
        $this->regex = $regex;
        $this->values = $values;
    }

    public function getRegex(): string {
        return $this->regex;
    }

    public function getValues(): array {
        return $this->values;
    }

    public static function fromArray($array): MatchAgainstRegexQuery {
        return new MatchAgainstRegexQuery($array['regex'], $array['values'] ?? []);
    }
}
