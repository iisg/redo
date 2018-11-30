<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class SubstringImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported): array {
        $start = $config['start'] ?? 0;
        $length = $config['length'] ?? PHP_INT_MAX;
        return array_map(
            function ($value) use ($length, $start) {
                return mb_substr($value, $start, $length);
            },
            $values
        );
    }
}
