<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;

class RemoveEmptyImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        return array_filter($values);
    }
}
