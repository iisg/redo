<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Respect\Validation\Validator;

class SimpleFileUploadTargetDirConstraint extends RespectValidationMetadataConstraint implements ConfigurableMetadataConstraint {
    public function getSupportedControls(): array {
        return [MetadataControl::FILE];
    }

    public function isConfigValid($config): bool {
        return is_string($config);
    }

    public function getValidator(Metadata $metadata, $metadataValue) {
        return Validator::alwaysValid();
    }
}
