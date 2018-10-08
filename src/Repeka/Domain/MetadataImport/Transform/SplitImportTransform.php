<?php
namespace Repeka\Domain\MetadataImport\Transform;

class SplitImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config): array {
        $separator = $config['separator'] ?? ',';
        return array_map(
            function ($element) use ($separator) {
                return is_string($element) ? explode($separator, $element) : $element;
            },
            $values
        );
    }
}
