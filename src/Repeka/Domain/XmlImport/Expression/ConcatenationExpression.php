<?php
namespace Repeka\Domain\XmlImport\Expression;

class ConcatenationExpression implements ValueExpression {
    /** @var array|ValueExpression[] */
    private $expressions;

    /**
     * @param ValueExpression[] $expressions
     */
    public function __construct(array $expressions) {
        $this->expressions = $expressions;
    }

    public function getRequiredTransformNames(): array {
        $names = [];
        foreach ($this->expressions as $expression) {
            $names = array_merge($names, $expression->getRequiredTransformNames());
        }
        return array_values(array_unique($names));
    }

    public function getRequiredSubfieldNames(): array {
        $names = [];
        foreach ($this->expressions as $expression) {
            $names = array_merge($names, $expression->getRequiredSubfieldNames());
        }
        return array_values(array_unique($names));
    }

    public function concatenate(array $subfields, array $transforms): string {
        $parts = array_map(function (ValueExpression $expression) use ($subfields, $transforms) {
            $values = $expression->evaluate($subfields, $transforms);
            return implode('', $values);
        }, $this->expressions);
        return implode('', $parts);
    }

    public function evaluate(array $subfields, array $transforms): array {
        return [$this->concatenate($subfields, $transforms)];
    }
}
