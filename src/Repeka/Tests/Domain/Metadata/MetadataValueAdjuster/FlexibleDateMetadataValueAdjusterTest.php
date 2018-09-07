<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataDateControl\FlexibleDate;
use Repeka\Domain\Entity\MetadataDateControl\MetadataDateControlMode;
use Repeka\Domain\Entity\MetadataValue;

class FlexibleDateMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    /** @var FlexibleDateMetadataValueAdjuster */
    private $metadataValueAdjuster;

    protected function setUp() {
        $this->metadataValueAdjuster = new FlexibleDateMetadataValueAdjuster();
    }

    public function testConvertFlexibleDateControlMetadataValuesToFlexibleData() {
        $expectedValue = new MetadataValue(
            (new FlexibleDate('2018-09-13T00:00:00', '2018-09-13T23:59:59', MetadataDateControlMode::DAY, null, '13.09.2018'))->toArray()
        );
        $actualValue = $this->metadataValueAdjuster->adjustMetadataValue(
            new MetadataValue(
                new FlexibleDate('2018-09-13T16:39:49+02:00', '2018-09-13T16:39:49+02:00', MetadataDateControlMode::DAY, null)
            ),
            MetadataControl::FLEXIBLE_DATE()
        );
        $this->assertEquals($expectedValue, $actualValue);
    }
}
