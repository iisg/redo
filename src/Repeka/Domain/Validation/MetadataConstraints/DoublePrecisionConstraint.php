<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Respect\Validation\Validator;

class DoublePrecisionConstraint extends RespectValidationMetadataConstraint implements ConfigurableMetadataConstraint {
    public function getSupportedControls(): array {
        return [MetadataControl::DOUBLE];
    }

    public function isConfigValid($precision): bool {
        return is_numeric($precision) && $precision >= 0;
    }

    public function getValidator(Metadata $metadata, $displayStrategy, $metadataValue) {
        return Validator::alwaysValid();
    }
}
