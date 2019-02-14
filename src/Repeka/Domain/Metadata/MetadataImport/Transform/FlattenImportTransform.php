<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Repeka\Domain\Metadata\MetadataImport\MetadataImportContext;
use Repeka\Domain\Utils\ArrayUtils;

class FlattenImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, ?MetadataImportContext $context = null): array {
        return ArrayUtils::flatten($values);
    }
}
