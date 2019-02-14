<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class JoinImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        $glue = $config['glue'] ?? ', ';
        if (count($values) == count(array_filter($values, 'is_string'))) {
            return [implode($glue, $values)];
        } else {
            $joinedValues = [];
            foreach ($values as $value) {
                if (is_array($value)) {
                    $value = $this->apply($value, $config, $dataBeingImported);
                }
                $joinedValues[] = $value;
            }
            return $joinedValues;
        }
    }
}
