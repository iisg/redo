<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

abstract class AbstractMetadataConstraint {
    abstract public function getConstraintName(): string;

    abstract public function validateArgument($argument): bool;

    abstract public function validateValue($argument, $input): bool;
}
