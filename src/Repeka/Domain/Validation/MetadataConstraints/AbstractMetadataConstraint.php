<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Stringy\StaticStringy;

abstract class AbstractMetadataConstraint {
    public function getConstraintName(): string {
        $reflectionClass = new \ReflectionClass($this);
        $withoutSuffix = preg_replace("/Constraint$/", '', $reflectionClass->getShortName());
        return StaticStringy::camelize($withoutSuffix);
    }

    /**
     * Specifies which controls this constraint validates.
     * @return string[]
     */
    abstract public function getSupportedControls(): array;

    /**
     * Validates constraint configuration in metadata definition when metadata is created or updated.
     */
    abstract public function isConfigValid($config): bool;

    public function validateAll(Metadata $metadata, $config, array $metadataValues) {
        foreach ($metadataValues as $metadataValue) {
            $this->validateSingle($metadata, $config, $metadataValue);
        }
    }

    abstract public function validateSingle(Metadata $metadata, $config, $metadataValue);
}
