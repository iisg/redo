<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use DateTime;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;

class TimestampMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var TimestampMetadataValueAdjuster */
    private $metadataValueAdjuster;

    protected function setUp() {
        $this->metadataValueAdjuster = new TimestampMetadataValueAdjuster();
    }

    public function testConvertTimestampControlMetadataValuesToCustomDateFormat() {
        $expectedValue = new MetadataValue((new DateTime('2018-09-13T16:39:49'))->format(DateTime::ATOM));
        $actualValue = $this->metadataValueAdjuster->adjustMetadataValue(
            new MetadataValue('2018-09-13T16:39:49'),
            $this->createMetadataMock()
        );
        $this->assertEquals($expectedValue, $actualValue);
    }
}
