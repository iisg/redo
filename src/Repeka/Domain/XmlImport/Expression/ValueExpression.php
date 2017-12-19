<?php
namespace Repeka\Domain\XmlImport\Expression;

use Repeka\Domain\XmlImport\Transform\Transform;

interface ValueExpression {
    /** @return string[] */
    public function getRequiredTransformNames(): array;

    /** @return string[] */
    public function getRequiredSubfieldNames(): array;

    /**
     * @param Subfield[] $subfields
     * @param Transform[] $transforms with their names in keys
     * @return string[]
     */
    public function evaluate(array $subfields, array $transforms): array;
}
