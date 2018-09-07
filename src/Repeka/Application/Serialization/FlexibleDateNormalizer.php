<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;

class FlexibleDateNormalizer extends AbstractNormalizer {

    /**
     * @param $entry FlexibleDate
     * @inheritdoc
     */
    public function normalize($entry, $format = null, array $context = []) {
        $data = [
            'from' => $entry->getFrom(),
            'to' => $entry->getTo(),
            'mode' => $entry->getMode(),
            'rangeMode' => $entry->getRangeMode(),
            'displayValue' => $entry->getDisplayValue(),
        ];
        return $data;
    }

    /** @inheritdoc */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof FlexibleDate;
    }
}
