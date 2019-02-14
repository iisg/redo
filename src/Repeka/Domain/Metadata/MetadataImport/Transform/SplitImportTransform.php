<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class SplitImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        $separator = $config['separator'] ?? ',';
        return array_map(
            function ($element) use ($separator) {
                return is_string($element) ? explode($separator, $element) : $element;
            },
            $values
        );
    }
}
