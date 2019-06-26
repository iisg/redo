<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Respect\Validation\Exceptions\ValidationException;

class IntegerMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::INTEGER;
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        $textValue = trim($value->getValue());
        $intValue = intval($textValue);
        if ($textValue && !$intValue && $textValue !== '0') {
            throw new ValidationException('Invalid integer value: ' . $value->getValue());
        }
        return $value->withNewValue($intValue);
    }
}
