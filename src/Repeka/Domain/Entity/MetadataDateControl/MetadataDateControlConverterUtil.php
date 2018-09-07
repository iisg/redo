<?php
namespace Repeka\Domain\Entity\MetadataDateControl;

use DateTime;

final class MetadataDateControlConverterUtil {

    private const FLEXIBLE_DATE_FORMAT = 'Y-m-d\TH:i:s';

    private function __construct() {
    }

    /**
     * @param string | int $from
     * @param string | int $to
     * @param string | MetadataDateControlMode $mode
     * @param string | MetadataDateControlMode $rangeMode
     * @return FlexibleDate
     */
    public static function convertDateToFlexibleDate($from, $to, $mode, $rangeMode): FlexibleDate {
        $from = self::toFlexibleDateFormat($from);
        $to = self::toFlexibleDateFormat($to);
        if ($mode == MetadataDateControlMode::RANGE) {
            $from = new DateTime($from);
            $to = new DateTime($to);
            $from = self::startOf($rangeMode, $from);
            $to = self::endOf($rangeMode, $to);
        } else {
            $to = new DateTime($from);
            $from = new DateTime($from);
            $from = self::startOf($mode, $from);
            $to = self::endOf($mode, $to);
        }
        $from = $from->format(self::FLEXIBLE_DATE_FORMAT);
        $to = $to->format(self::FLEXIBLE_DATE_FORMAT);
        return new FlexibleDate($from, $to, $mode, $rangeMode);
    }

    /**
     * @var integer | string $date
     * @return string
     */
    private static function toFlexibleDateFormat($date) {
        return is_integer($date)
            ? (new DateTime())->setTimestamp($date)->format(self::FLEXIBLE_DATE_FORMAT)
            : (new DateTime($date))->format(self::FLEXIBLE_DATE_FORMAT);
    }

    /**
     * @param string | MetadataDateControlMode $mode
     * @param string | DateTime $from
     * @return string | DateTime
     */
    private static function startOf($mode, $from) {
        switch ($mode) {
            case MetadataDateControlMode::YEAR:
                return $from->setDate($from->format('Y'), 1, 1)->setTime(0, 0, 0);
                break;
            case MetadataDateControlMode::MONTH:
                $from = date('Y-m-1', strtotime($from->format(DateTime::ATOM)));
                return new DateTime($from);
                break;
            case MetadataDateControlMode::DAY:
                return $from->setTime(0, 0, 0);
                break;
            case MetadataDateControlMode::DATE_TIME:
                return $from;
                break;
        }
    }

    /**
     * @param string | MetadataDateControlMode $mode
     * @param string | DateTime $to
     * @return string | DateTime
     */
    private static function endOf($mode, $to) {
        switch ($mode) {
            case MetadataDateControlMode::YEAR:
                return $to->setDate($to->format('Y'), 12, 31)->setTime(23, 59, 59);
                break;
            case MetadataDateControlMode::MONTH:
                $to = $to->format('Y-m-t');
                return (new DateTime($to))->setTime(23, 59, 59);
                break;
            case MetadataDateControlMode::DAY:
                return $to->setTime(23, 59, 59);
                break;
            case MetadataDateControlMode::DATE_TIME:
                return $to;
                break;
        }
    }

    public static function convertDateToAtomFormat($value): string {
        return (new DateTime($value))->format(DateTime::ATOM);
    }
}
