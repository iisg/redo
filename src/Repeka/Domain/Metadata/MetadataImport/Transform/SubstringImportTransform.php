<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class SubstringImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
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
