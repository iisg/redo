<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Constants\RelationshipSelectorType;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Respect\Validation\Validator;

class RelationshipSelectorTypeConstraint extends RespectValidationMetadataConstraint implements ConfigurableMetadataConstraint {

    public function getSupportedControls(): array {
        return [MetadataControl::RELATIONSHIP];
    }

    public function isConfigValid($config): bool {
        return Validator::stringType()->validate($config) && RelationshipSelectorType::isValid($config);
    }

    public function getValidator(Metadata $metadata, $metadataValue) {
        return Validator::alwaysValid();
    }
}
