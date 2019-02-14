<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\Assertion;
use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class DefaultValueImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        Assertion::keyExists($config, 'value');
        $defaultValue = $config['value'];
        $override = $config['override'] ?? false;
        if ($override || count($values) === 0) {
            if (!is_array($defaultValue)) {
                return [$defaultValue];
            } else {
                return $defaultValue;
            }
        } else {
            return $values;
        }
    }
}
