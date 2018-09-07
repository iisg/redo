<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;

interface MetadataValueAdjuster {
    public function supports(string $control): bool;

    public function adjustMetadataValue(MetadataValue $value, MetadataControl $control): MetadataValue;
}
