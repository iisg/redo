<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class ImportTransformComposite {
    /** @var ImportTransform[] */
    private $transforms = [];

    /** @param ImportTransform[] $transforms */
    public function __construct(iterable $transforms) {
        foreach ($transforms as $transform) {
            $this->transforms[$transform->getName()] = $transform;
        }
    }

    public function apply(array $values, array $config, $data, ?MetadataImportContext $context = null): array {
        $name = $config['name'];
        Assertion::keyExists($this->transforms, $name, "Invalid transform name: $name");
        return $this->transforms[$name]->apply($values, $config, $data, $context);
    }
}
