<?php
namespace Repeka\Domain\Entity\MetadataDateControl;

use DateTime;

class FlexibleDateImportConverterUtil {

    public static function importInputToFlexibleDateConverter(string $value): ?array {
        if (preg_match('/^.*-.*$/', $value)) { // examples:  '1888-1999'; '1444- '  //'/^([0-9]*| )-([0-9]*| )$/'
            $values = explode('-', $value);
            $fromArray = FlexibleDateImportConverterUtil::convertDate($values[0]);
            $toArray = FlexibleDateImportConverterUtil::convertDate($values[1]);
            if (is_array($fromArray) && is_array($toArray)) {
                return FlexibleDateImportConverterUtil::rangeFlexibleDate($fromArray, $toArray);
            }
        } else {
            $dateArray = FlexibleDateImportConverterUtil::convertDate($value);
            if (is_array($dateArray)) {
                return FlexibleDateImportConverterUtil::yearFlexibleDate($dateArray);
            }
        }
        return null;
    }

    /** @SuppressWarnings(PHPMD.CyclomaticComplexity) */
    public static function convertDate(string $value): ?array {
        if (preg_match('/^[0-9]+$/', $value)) {
            return [DateTime::createFromFormat('Y', $value)->format(DateTime::ATOM)];
        } elseif (preg_match('/^ *$/', $value)) {
            return [null];
        } elseif (preg_match('/^dr\. [0-9]+$/', $value)) { //dr. 1923
            return FlexibleDateImportConverterUtil::convertDate(preg_replace('/dr\. /', '', $value));
        } elseif (preg_match('/^ca [0-9]+$/', $value)) { // ca. 1900
            return FlexibleDateImportConverterUtil::convertDate(preg_replace('/ca /', '', $value));
        } elseif (preg_match('/^cop. [0-9]+$/', $value)) { // cop. 1939
            return FlexibleDateImportConverterUtil::convertDate(preg_replace('/cop\. /', '', $value));
        } elseif (preg_match('/^[0-9]+\.\.$/', $value)) { // 19..
            return FlexibleDateImportConverterUtil::convertDate(preg_replace('/\.\./', '00', $value));
        } elseif (preg_match('/^\?$/', $value)) { // ?
            return [null];
        } elseif (preg_match('/^post [0-9]+$/', $value)) { //post 1921
            return null;
        } elseif (preg_match('/^[0-9]+\?$/', $value)) { // 1943?
            return [null];
        } elseif (preg_match('/^[0-9]+\/[0-9]+$/', $value)) { // 1912/13
            return null;
        } elseif (preg_match('/^[0-9]+uu$/', $value)) { // 18uu
            return null;
        }
        return null;
    }

    public static function rangeFlexibleDate($fromArray, $toArray): array {
        return MetadataDateControlConverterUtil::convertDateToFlexibleDate(
            $fromArray[0],
            $toArray[0],
            MetadataDateControlMode::RANGE,
            MetadataDateControlMode::YEAR
        )->toArray();
    }

    public static function yearFlexibleDate($dateArray): array {
        return MetadataDateControlConverterUtil::convertDateToFlexibleDate(
            $dateArray[0],
            null,
            MetadataDateControlMode::YEAR,
            null
        )->toArray();
    }
}
