<?php
namespace Repeka\Domain\MetadataImport\Transform;

use Assert\Assertion;

class ImportTransformComposite {
    /** @var ImportTransform[] */
    private $transforms = [];

    public function register(ImportTransform $transform) {
        $this->transforms[$transform->getName()] = $transform;
    }

    public function apply(array $values, array $config): array {
        $name = $config['name'];
        Assertion::keyExists($this->transforms, $name, "Invalid transform name: $name");
        return $this->transforms[$name]->apply($values, $config);
    }
}
