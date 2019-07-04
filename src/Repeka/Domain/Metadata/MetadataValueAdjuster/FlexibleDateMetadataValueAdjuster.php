<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataValue;

class FlexibleDateMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::FLEXIBLE_DATE || $control == MetadataControl::DATE_RANGE;
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        return $value->withNewValue($this->convertDateControlMetadataValuesToFlexibleDate($value->getValue()));
    }

    /**
     * @param FlexibleDate | array $value
     * @return array
     */
    private function convertDateControlMetadataValuesToFlexibleDate($value): ?array {
        if (is_string($value)) {
            $value = ['from' => $value];
        }
        if (is_array($value)) {
            $rangeMode = array_key_exists('rangeMode', $value) ? $value['rangeMode'] : null;
            $value = new FlexibleDate(
                $value['from'] ?? $value['date'] ?? null,
                $value['to'] ?? null,
                $value['mode'] ?? null,
                $rangeMode
            );
        }
        if (!($value instanceof FlexibleDate)) {
            return null;
        }
        return MetadataDateControlConverterUtil::convertDateToFlexibleDate(
            $value->getFrom(),
            $value->getTo(),
            $value->getMode(),
            $value->getRangeMode()
        )->toArray();
    }
}
