<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class ArrayUniqueImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported): array {
        return array_unique($values);
    }
}
