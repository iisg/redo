<?php
namespace Repeka\Domain\Metadata\SearchValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;

interface SearchValueAdjuster {
    public function supports(MetadataControl $control): bool;

    public function adjustSearchValue($value, MetadataControl $control);
}
