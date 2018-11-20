<?php
namespace Repeka\Domain\Metadata\SearchValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;

class DefaultSearchValueAdjuster implements SearchValueAdjuster {
    public function supports(MetadataControl $control): bool {
        return false;
    }

    public function adjustSearchValue($value, MetadataControl $control) {
        return $value;
    }
}
