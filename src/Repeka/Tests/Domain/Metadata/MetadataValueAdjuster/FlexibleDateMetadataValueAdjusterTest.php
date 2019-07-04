<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;

class FlexibleDateMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FlexibleDateMetadataValueAdjuster */
    private $metadataValueAdjuster;

    protected function setUp() {
        $this->metadataValueAdjuster = new FlexibleDateMetadataValueAdjuster();
    }

    /** @dataProvider flexibleDateExamples */
    public function testConvertFlexibleDateControlMetadataValuesToFlexibleData($input, $expectedOutput) {
        $expectedValue = new MetadataValue($expectedOutput);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FLEXIBLE_DATE());
        $actualValue = $this->metadataValueAdjuster->adjustMetadataValue(new MetadataValue($input), $metadata);
        $this->assertEquals($expectedValue, $actualValue);
    }

    public function flexibleDateExamples() {
        return [
            [
                new FlexibleDate('2018-09-13T16:39:49+02:00', '2018-09-13T16:39:49+02:00', MetadataDateControlMode::DAY, null),
                (new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null))->toArray(),
            ],
            [
                new FlexibleDate(
                    '2018-09-13T16:39:49+02:00',
                    '2018-09-13T16:39:49+02:00',
                    MetadataDateControlMode::RANGE(),
                    MetadataDateControlMode::DAY()
                ),
                (new FlexibleDate(
                    '2018-09-13T00:00:00',
                    '2018-09-13T23:59:59',
                    MetadataDateControlMode::RANGE(),
                    MetadataDateControlMode::DAY()
                ))->toArray(),
            ],
            [
                new FlexibleDate('2018-09-13T16:39:49+02:00', '2018-09-15T16:39:49+02:00', MetadataDateControlMode::DAY, null),
                (new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null))->toArray(),
            ],
            [
                new FlexibleDate('2018-09-13T16:39:49+02:00', 'whateve', MetadataDateControlMode::DAY, null),
                (new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00', 'mode' => 'day'],
                (new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00'],
                (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', MetadataDateControlMode::DATE_TIME, null))->toArray(),
            ],
            [
                ['date' => '2018-09-13T16:39:49+02:00'],
                (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', MetadataDateControlMode::DATE_TIME, null))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00', 'mode' => 'month'],
                (new FlexibleDate('2018-09-01T00:00:00', '2018-09-30T23:59:59', MetadataDateControlMode::MONTH, null))->toArray(),
            ],
            [
                '2018-09-13T16:39:49+02:00',
                (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', MetadataDateControlMode::DATE_TIME, null))->toArray(),
            ],
            [
                '2018-09-13T16:39:49+08:00',
                (new FlexibleDate('2018-09-13T16:39:49', '2018-09-13T16:39:49', MetadataDateControlMode::DATE_TIME, null))->toArray(),
            ],
            [
                '2018-09-13',
                (new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T00:00:00', MetadataDateControlMode::DATE_TIME, null))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00', 'to' => '2018-09-13T16:49:49+02:00'],
                (new FlexibleDate(
                    '2018-09-13T16:39:49',
                    '2018-09-13T16:49:49',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::DATE_TIME
                ))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00', 'to' => '2018-09-13T16:49:49+02:00', 'rangeMode' => 'day'],
                (new FlexibleDate(
                    '2018-09-13T00:00:00',
                    '2018-09-13T23:59:59',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::DAY
                ))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00', 'to' => '2018-09-14T16:49:49+02:00', 'rangeMode' => 'day'],
                (new FlexibleDate(
                    '2018-09-13T00:00:00',
                    '2018-09-14T23:59:59',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::DAY
                ))->toArray(),
            ],
            [
                ['from' => '2018-09-13T16:39:49+02:00', 'to' => '2018-09-14T16:49:49+02:00', 'rangeMode' => 'year'],
                (new FlexibleDate(
                    '2018-01-01T00:00:00',
                    '2018-12-31T23:59:59',
                    MetadataDateControlMode::RANGE,
                    MetadataDateControlMode::YEAR
                ))->toArray(),
            ],
        ];
    }
}
