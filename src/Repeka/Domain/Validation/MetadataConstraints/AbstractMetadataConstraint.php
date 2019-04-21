<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceEntity;
use Stringy\StaticStringy;

abstract class AbstractMetadataConstraint {
    public function getConstraintName(): string {
        $reflectionClass = new \ReflectionClass($this);
        $withoutSuffix = preg_replace("/(Metadata)?Constraint$/", '', $reflectionClass->getShortName());
        return StaticStringy::camelize($withoutSuffix);
    }

    /**
     * Specifies which controls this constraint validates.
     * @return string[]
     */
    abstract public function getSupportedControls(): array;

    public function validateAll(Metadata $metadata, array $metadataValues, ResourceEntity $resource = null): void {
        foreach ($metadataValues as $metadataValue) {
            $this->validateSingle($metadata, $metadataValue, $resource);
        }
    }

    abstract public function validateSingle(Metadata $metadata, $metadataValue, ResourceEntity $resource = null): void;
}
