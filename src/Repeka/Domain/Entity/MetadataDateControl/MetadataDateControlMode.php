<?php
namespace Repeka\Domain\Entity\MetadataDateControl;

use MyCLabs\Enum\Enum;

/**
 * @method static MetadataDateControlMode YEAR()
 * @method static MetadataDateControlMode MONTH()
 * @method static MetadataDateControlMode DAY()
 * @method static MetadataDateControlMode DATE_TIME()
 * @method static MetadataDateControlMode RANGE()
 **/
class MetadataDateControlMode extends Enum {
    const YEAR = 'year';
    const MONTH = 'month';
    const DAY = 'day';
    const DATE_TIME = 'date_time';
    const RANGE = 'range';

    public static function rangeModes(): array {
        return array_filter(self::toArray(), function ($mode) {
            return $mode != MetadataDateControlMode::RANGE;
        });
    }
}
