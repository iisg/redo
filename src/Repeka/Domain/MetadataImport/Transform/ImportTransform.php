<?php
namespace Repeka\Domain\MetadataImport\Transform;

interface ImportTransform {
    public function apply(array $values, array $config): array;

    public function getName(): string;
}
