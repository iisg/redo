<?php
namespace Repeka\Domain\XmlImport\Transform;

class JoinTransform implements Transform {
    /** @var string */
    private $glue;

    public function forArguments(string $glue): JoinTransform {
        $instance = new JoinTransform();
        $instance->glue = $glue;
        return $instance;
    }

    public function apply(array $values): array {
        return [implode($this->glue, $values)];
    }
}
