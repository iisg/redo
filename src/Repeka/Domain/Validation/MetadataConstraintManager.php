<?php
namespace Repeka\Domain\Validation;

use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;

interface MetadataConstraintManager {
    public function get(string $constraintName): AbstractMetadataConstraint;

    /** @return string[] */
    public function getSupportedConstraintNamesForControl(string $controlName): array;
}
