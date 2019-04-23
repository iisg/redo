<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataValue;

class TimestampMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return $control == MetadataControl::TIMESTAMP;
    }

    public function adjustMetadataValue(MetadataValue $value, Metadata $metadata): MetadataValue {
        $date = MetadataDateControlConverterUtil::convertDateToAtomFormat($value->getValue());
        return $value->withNewValue($date);
    }
}
