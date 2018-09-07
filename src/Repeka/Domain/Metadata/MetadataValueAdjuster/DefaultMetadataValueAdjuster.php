<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;

class DefaultMetadataValueAdjuster implements MetadataValueAdjuster {
    public function supports(string $control): bool {
        return false;
    }

    public function adjustMetadataValue(MetadataValue $value, MetadataControl $control): MetadataValue {
        return $value;
    }
}
