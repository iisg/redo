<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

class JoinImportTransform extends AbstractImportTransform {
    public function apply(array $values, array $config): array {
        $glue = $config['glue'] ?? ', ';
        return [implode($glue, $values)];
    }
}
