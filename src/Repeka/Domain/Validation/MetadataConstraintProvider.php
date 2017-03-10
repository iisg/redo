<?php
namespace Repeka\Domain\Validation;

use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

interface MetadataConstraintProvider {
    public function get(string $constraintName): AbstractMetadataConstraint;
}
