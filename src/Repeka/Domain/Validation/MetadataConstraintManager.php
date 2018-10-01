<?php
namespace Repeka\Domain\Validation;

use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\MetadataConstraints\ConfigurableMetadataConstraint;

interface MetadataConstraintManager {
    /** @return AbstractMetadataConstraint|ConfigurableMetadataConstraint */
    public function get(string $constraintName);

    /** * @return AbstractMetadataConstraint[] */
    public function getMandatoryConstraintsForControl(string $controlName): array;

    /** @return string[] */
    public function getSupportedConstraintNamesForControl(string $controlName): array;
}
