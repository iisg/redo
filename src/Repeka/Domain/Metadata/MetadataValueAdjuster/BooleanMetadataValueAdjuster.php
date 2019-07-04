<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;

class BooleanMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::BOOLEAN;
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        $input = $value->getValue();
        if (is_string($input)) {
            $input = trim($input);
        }
        if (strtolower($input) === 'false') {
            $input = false;
        }
        return $value->withNewValue(!!$input);
    }
}
