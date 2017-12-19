<?php
namespace Repeka\Domain\XmlImport\Expression;

class LiteralExpression implements ValueExpression {
    /** @var string */
    private $literal;

    public function __construct(string $literal) {
        $this->literal = $literal;
    }

    public function getRequiredTransformNames(): array {
        return [];
    }

    public function getRequiredSubfieldNames(): array {
        return [];
    }

    public function evaluate(array $subfields, array $transforms): array {
        return [$this->literal];
    }
}
