<?php
namespace Repeka\Tests\Domain\Entity\MetadataDateControl;

use DateTime;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDateImportConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;

class FlexibleDateImportConverterUtilTest extends \PHPUnit_Framework_TestCase {

    /** @dataProvider fromArrayExamples */
    public function testFromArray(string $value, $expectedOutput) {
        $normalized = FlexibleDateImportConverterUtil::importInputToFlexibleDateConverter($value);
        $this->assertEquals($expectedOutput, $normalized);
    }

    /** @SuppressWarnings("PHPMD.ExcessiveMethodLength") */
    public function fromArrayExamples(): array {

        return [
            [
                '1888-1999',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1888')->format(DateTime::ATOM),
                    DateTime::createFromFormat('Y', '1999')->format(DateTime::ATOM),
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                )->toArray(),
            ],
            [
                '1888-',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1888')->format(DateTime::ATOM),
                    null,
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                )->toArray(),
            ],
            [
                '1888- ',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1888')->format(DateTime::ATOM),
                    null,
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                )->toArray(),
            ],
            [
                ' -1888',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    null,
                    DateTime::createFromFormat('Y', '1888')->format(DateTime::ATOM),
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                )->toArray(),
            ],
            [
                '1888',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1888')->format(DateTime::ATOM),
                    null,
                    MetadataDateControlMode::YEAR,
                    null
                )->toArray(),
            ],
            [
                '178dsgdf',
                null,
            ],
            [
                '178-12aad',
                null,
            ],
            [
                'dr. 1923',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1923')->format(DateTime::ATOM),
                    null,
                    MetadataDateControlMode::YEAR,
                    null
                )->toArray(),
            ],
            [
                'ca 1900',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1900')->format(DateTime::ATOM),
                    null,
                    MetadataDateControlMode::YEAR,
                    null
                )->toArray(),
            ],
            [
                'cop. 1939',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1939')->format(DateTime::ATOM),
                    null,
                    MetadataDateControlMode::YEAR,
                    null
                )->toArray(),
            ],
            [
                'post 1921',
                null,
            ],
            [
                '?-1945',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    null,
                    DateTime::createFromFormat('Y', '1945')->format(DateTime::ATOM),
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                )->toArray(),
            ],
            [
                '1912/13-1913/14',
                null,
            ],
            [
                '18..-19..',
                MetadataDateControlConverterUtil::convertDateToFlexibleDate(
                    DateTime::createFromFormat('Y', '1800')->format(DateTime::ATOM),
                    DateTime::createFromFormat('Y', '1900')->format(DateTime::ATOM),
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                )->toArray(),
            ],
        ];
    }
}
