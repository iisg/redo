<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

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

    /**
     * Validates constrained value using metadata-specific configuration.
     */
    abstract public function isValueValid($config, $input): bool;
}
