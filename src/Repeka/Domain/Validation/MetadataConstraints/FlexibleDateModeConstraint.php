<?php
namespace Repeka\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Respect\Validation\Validator;

class FlexibleDateModeConstraint extends RespectValidationMetadataConstraint implements ConfigurableMetadataConstraint {

    const FLEXIBLE_MODE = 'flexible';

    public function getSupportedControls(): array {
        return [MetadataControl::FLEXIBLE_DATE, MetadataControl::DATE_RANGE];
    }

    public function isConfigValid($config): bool {
        return !$config || Validator::equals(self::FLEXIBLE_MODE)->validate($config)
            || Validator::in(MetadataDateControlMode::rangeModes())->validate($config);
    }

    public function getValidator(Metadata $metadata, $metadataValue) {
        return Validator::alwaysValid();
    }
}
