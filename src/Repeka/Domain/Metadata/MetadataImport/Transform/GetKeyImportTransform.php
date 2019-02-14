<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class GetKeyImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        Assertion::keyExists($config, 'key');
        Assertion::allIsArray($values);
        return array_map(
            function ($array) use ($config) {
                return $array[$config['key']] ?? '';
            },
            $values
        );
    }
}
