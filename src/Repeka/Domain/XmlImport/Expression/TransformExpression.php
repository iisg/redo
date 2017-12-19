<?php
namespace Repeka\Domain\XmlImport\Expression;

use Repeka\Domain\XmlImport\Transform\Transform;

class TransformExpression implements ValueExpression {
    /** @var ValueExpression */
    private $input;
    /** @var string */
    private $transformName;

    public function __construct(ValueExpression $input, string $transformName) {
        $this->input = $input;
        $this->transformName = $transformName;
    }

    public function getRequiredTransformNames(): array {
        $names = $this->input->getRequiredTransformNames();
        $names[] = $this->transformName;
        return array_values(array_unique($names));
    }

    public function getRequiredSubfieldNames(): array {
        return $this->input->getRequiredSubfieldNames();
    }

    public function evaluate(array $subfields, array $transforms): array {
        /** @var Transform $transform */
        $transform = $transforms[$this->transformName];
        $evaluatedInput = $this->input->evaluate($subfields, $transforms);
        return $transform->apply($evaluatedInput);
    }
}
