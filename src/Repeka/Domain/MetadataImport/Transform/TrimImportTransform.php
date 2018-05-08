<?php
namespace Repeka\Domain\MetadataImport\Transform;

class TrimImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config): array {
        return array_map(
            function ($value) use ($config) {
                return trim($value, $config['characters'] ?? " \t\n\r\0\x0B");
            },
            $values
        );
    }
}
