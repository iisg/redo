<?php
namespace Repeka\Domain\Entity\MetadataDateControl;

use Assert\Assertion;
use DateTime;
use Repeka\Domain\Entity\MetadataValue;

final class MetadataDateControlConverterUtil {

    private const FLEXIBLE_DATE_FORMAT = 'Y-m-d\TH:i:s';
    private const TIMESTAMP_DATE_FORMAT = DateTime::ATOM;

    private function __construct() {
    }

    /**
     * @param string | int | null $from
     * @param string | int | null $to
     * @param string | MetadataDateControlMode $mode
     * @param string | MetadataDateControlMode $rangeMode
     * @return FlexibleDate
     */
    public static function convertDateToFlexibleDate($from, $to, $mode, $rangeMode): FlexibleDate {
        $from = !is_null($from) ? self::toFlexibleDateFormat($from) : $from;
        $to = !is_null($to) && $rangeMode ? self::toFlexibleDateFormat($to) : $to;
        return ($mode == MetadataDateControlMode::RANGE)
            ? self::getFlexibleRangeDateMode($from, $to, $mode, $rangeMode, self::FLEXIBLE_DATE_FORMAT)
            : self::getFlexibleDateMode($from, $mode, self::FLEXIBLE_DATE_FORMAT);
    }

    /**
     * @param string | int | null $from
     * @param string | int | null $to
     * @param string | MetadataDateControlMode $mode
     * @param string | MetadataDateControlMode $rangeMode
     * @return FlexibleDate
     */
    public static function convertDateToFlexibleDateWithTimestampDates($from, $to, $mode, $rangeMode): FlexibleDate {
        return ($mode == MetadataDateControlMode::RANGE)
            ? self::getFlexibleRangeDateMode($from, $to, $mode, $rangeMode, self::TIMESTAMP_DATE_FORMAT)
            : self::getFlexibleDateMode($from, $mode, self::TIMESTAMP_DATE_FORMAT);
    }

    /**
     * @param string | int | null $from
     * @param string | int | null $to
     * @param string | MetadataDateControlMode $mode
     * @param string | MetadataDateControlMode $rangeMode
     * @param string $dateFormat
     * @return FlexibleDate
     */
    private static function getFlexibleRangeDateMode($from, $to, $mode, $rangeMode, $dateFormat = self::FLEXIBLE_DATE_FORMAT) {
        if (!is_null($from)) {
            $from = !is_null($from) ? new DateTime($from) : null;
            $from = self::startOf($rangeMode, $from);
            $from = $from->format($dateFormat);
        }
        if (!is_null($to)) {
            $to = !is_null($to) ? new DateTime($to) : null;
            $to = self::endOf($rangeMode, $to);
            $to = $to->format($dateFormat);
        }
        return new FlexibleDate($from, $to, $mode, $rangeMode);
    }

    /**
     * @param string | int | null $from
     * @param string | MetadataDateControlMode $mode
     * @param string $dateFormat
     * @return FlexibleDate
     */
    private static function getFlexibleDateMode($from, $mode, $dateFormat = self::FLEXIBLE_DATE_FORMAT) {
        $to = new DateTime($from);
        $from = new DateTime($from);
        $from = self::startOf($mode, $from);
        $to = self::endOf($mode, $to);
        $from = $from->format($dateFormat);
        $to = $to->format($dateFormat);
        return new FlexibleDate($from, $to, $mode, null);
    }

    /**
     * @return string
     * @var integer | string $date
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

    /**
     * @param string|DateTime $value
     * @return string
     */
    public static function convertDateToAtomFormat($value): string {
        if ($value instanceof DateTime) {
            $dateTime = $value;
        } elseif ($value instanceof MetadataValue) {
            $value = $value->getValue();
        }
        if (!isset($dateTime)) {
            Assertion::string($value, 'Invalid date given:' . var_export($value, true));
            $dateTime = new DateTime($value);
        }
        $dateTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        return $dateTime->format(DateTime::ATOM);
    }
}
