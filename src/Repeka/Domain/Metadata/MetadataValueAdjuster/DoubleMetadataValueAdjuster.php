<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Respect\Validation\Exceptions\ValidationException;

class DoubleMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::DOUBLE;
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        $textValue = trim($this->replaceCommaWithDot($value->getValue()));
        $floatValue = floatval($textValue);
        if ($textValue && !$floatValue && $textValue{0} !== '0') {
            throw new ValidationException('Invalid double value: ' . $value->getValue());
        }
        return $value->withNewValue($floatValue);
    }

    private function replaceCommaWithDot($value) {
        return is_string($value)
            ? str_replace(',', '.', $value)
            : $value;
    }
}
