<?php
namespace Repeka\Tests\Domain\Entity\MetadataDateControl;

use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlConverterUtil;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;

class MetadataDateControlConverterUtilTest extends \PHPUnit_Framework_TestCase {

    /** @dataProvider fromArrayExamplesForFlexibleDate */
    public function testFromArrayForFlexibleDate(array $input, FlexibleDate $expectedOutput) {
        $normalized = MetadataDateControlConverterUtil::convertDateToFlexibleDate(
            $input['from'],
            $input['to'],
            $input['mode'],
            $input['rangeMode']
        );
        $this->assertEquals($expectedOutput, $normalized);
    }

    public function fromArrayExamplesForFlexibleDate(): array {
        $dateFromAtom = '2018-09-13T16:39:49+02:00';
        $dateToAtom = '2018-09-13T16:39:49+02:00';
        return [
            [
                ['from' => $dateFromAtom, 'to' => $dateToAtom, 'mode' => MetadataDateControlMode::YEAR, 'rangeMode' => null],
                new FlexibleDate('2018-01-01T00:00:00', '2018-12-31T23:59:59', MetadataDateControlMode::YEAR, null),
            ],
            [
                ['from' => $dateFromAtom, 'to' => $dateToAtom, 'mode' => MetadataDateControlMode::MONTH, 'rangeMode' => null],
                new FlexibleDate('2018-09-01T00:00:00', '2018-09-30T23:59:59', MetadataDateControlMode::MONTH, null),
            ],
            [
                ['from' => $dateFromAtom, 'to' => $dateToAtom, 'mode' => MetadataDateControlMode::DAY, 'rangeMode' => null],
                new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null),
            ],
            [
                ['from' => $dateFromAtom, 'to' => $dateToAtom, 'mode' => MetadataDateControlMode::DATE_TIME, 'rangeMode' => null],
                new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', MetadataDateControlMode::DATE_TIME, null),
            ],
            [
                [
                    'from' => $dateFromAtom,
                    'to' => '2018-09-15T16:32:50+02:00',
                    'mode' => MetadataDateControlMode::RANGE,
                    'rangeMode' => MetadataDateControlMode::DAY,
                ],
                new FlexibleDate(
                    '2018-09-13T00:00:00',
                    '2018-09-15T23:59:59',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::DAY
                ),
            ],
            [
                [
                    'from' => $dateFromAtom,
                    'to' => '2018-10-15T16:32:50+02:00',
                    'mode' => MetadataDateControlMode::RANGE,
                    'rangeMode' => MetadataDateControlMode::MONTH,
                ],
                new FlexibleDate(
                    '2018-09-01T00:00:00',
                    '2018-10-31T23:59:59',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::MONTH
                ),
            ],
            [
                [
                    'from' => $dateFromAtom,
                    'to' => '2018-10-15T16:32:50+02:00',
                    'mode' => MetadataDateControlMode::RANGE,
                    'rangeMode' => MetadataDateControlMode::DATE_TIME,
                ],
                new FlexibleDate(
                    '2018-09-13T16:39:49',
                    '2018-10-15T16:32:50',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::DATE_TIME
                ),
            ],
            [
                [
                    'from' => $dateFromAtom,
                    'to' => null,
                    'mode' => MetadataDateControlMode::RANGE,
                    'rangeMode' => MetadataDateControlMode::YEAR,
                ],
                new FlexibleDate(
                    '2018-01-01T00:00:00',
                    null,
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                ),
            ],
            [
                ['from' => $dateFromAtom, 'to' => '2018-10-15T16:32:50+02:00', 'mode' => MetadataDateControlMode::DAY, 'rangeMode' => null],
                new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null),
            ],
            [
                ['from' => 1536796811, 'to' => 1536883199, 'mode' => MetadataDateControlMode::DAY, 'rangeMode' => null],
                new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null),
            ],
        ];
    }

    /** @dataProvider fromArrayExamplesForFlexibleDateWithTimestampDates */
    public function testFromArrayForFlexibleDateWithTimestampDates(array $input, FlexibleDate $expectedOutput) {
        $normalized = MetadataDateControlConverterUtil::convertDateToFlexibleDateWithTimestampDates(
            $input['from'],
            $input['to'],
            $input['mode'],
            $input['rangeMode']
        );
        $this->assertEquals($expectedOutput, $normalized);
    }

    public function fromArrayExamplesForFlexibleDateWithTimestampDates(): array {
        return [
            [
                [
                    'from' => '2018-09-13T16:39:49+02:00',
                    'to' => '2018-09-13T16:39:49+02:00',
                    'mode' => MetadataDateControlMode::YEAR,
                    'rangeMode' => null,
                ],
                new FlexibleDate('2018-01-01T00:00:00+02:00', '2018-12-31T23:59:59+02:00', MetadataDateControlMode::YEAR, null),
            ],
            [
                [
                    'from' => '2018-01-01T00:00:05+02:00', // equivalent to 2017-12-31T22:00:05+00:00
                    'to' => '2018-09-13T16:39:49+02:00',
                    'mode' => MetadataDateControlMode::YEAR,
                    'rangeMode' => null,
                ],
                new FlexibleDate('2018-01-01T00:00:00+02:00', '2018-12-31T23:59:59+02:00', MetadataDateControlMode::YEAR, null),
            ],
            [
                [
                    'from' => '2018-01-01T00:00:05-08:00',
                    'to' => '2018-12-31T20:00:49-08:00', // equivalent to 2019-01-01T04:00:49+00:00
                    'mode' => MetadataDateControlMode::YEAR,
                    'rangeMode' => null,
                ],
                new FlexibleDate('2018-01-01T00:00:00-08:00', '2018-12-31T23:59:59-08:00', MetadataDateControlMode::YEAR, null),
            ],
        ];
    }
}
