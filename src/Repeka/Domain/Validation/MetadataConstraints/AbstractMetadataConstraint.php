<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Exception\DomainException;
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

    public function isMandatory(): bool {
        return false;
    }

    public function validateAll(Metadata $metadata, $config, array $metadataValues) {
        foreach ($metadataValues as $metadataValue) {
            $this->validateSingle($metadata, $config, $metadataValue);
        }
    }

    abstract public function validateSingle(Metadata $metadata, $config, $metadataValue);
}
