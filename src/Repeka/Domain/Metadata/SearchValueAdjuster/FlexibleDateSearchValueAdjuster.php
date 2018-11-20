<?php
namespace Repeka\Domain\Metadata\SearchValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;

class FlexibleDateSearchValueAdjuster implements SearchValueAdjuster {
    public function supports(MetadataControl $control): bool {
        return in_array($control->getValue(), [MetadataControl::FLEXIBLE_DATE, MetadataControl::DATE_RANGE]);
    }

    public function adjustSearchValue($value, MetadataControl $control) {
        $to = $this->getDatePartValue($value, 'to');
        $from = $this->getDatePartValue($value, 'from');
        $rangeMode = $this->getDatePartValue($value, 'rangeMode');
        if ($to || $from) {
            $flexibleDateArray = MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                $from,
                $to,
                MetadataDateControlMode::RANGE,
                $rangeMode ?? MetadataDateControlMode::DAY
            )->toArray();
            $adjustedValue = [];
            if (isset($flexibleDateArray['from']) and !is_null($flexibleDateArray['from'])) {
                $adjustedValue['gte'] = $flexibleDateArray['from'];
            }
            if (isset($flexibleDateArray['to']) and !is_null($flexibleDateArray['to'])) {
                $adjustedValue['lte'] = $flexibleDateArray['to'];
            }
            return !empty($adjustedValue) ? $adjustedValue : null;
        }
        return null;
    }

    private function getDatePartValue($value, $part) {
        return isset($value[$part]) && $value[$part] ? $value[$part] : null;
    }
}
