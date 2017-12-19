<?php
namespace Repeka\Domain\XmlImport\Expression;

class SubfieldExpression implements ValueExpression {
    /** @var string */
    private $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function getRequiredTransformNames(): array {
        return [];
    }

    public function getRequiredSubfieldNames(): array {
        return [$this->name];
    }

    public function evaluate(array $subfields, array $transforms): array {
        $subfieldsMatchingName = array_filter($subfields, function (Subfield $subfield) {
            return $subfield->getName() == $this->name;
        });
        return array_values(array_map(function (Subfield $subfield) {
            return $subfield->getContent();
        }, $subfieldsMatchingName));
    }
}
