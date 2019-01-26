<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

interface ImportTransform {
    public function apply(array $values, array $config, array $dataBeingImported, string $parentMetadataValue = null): array;

    public function getName(): string;
}
