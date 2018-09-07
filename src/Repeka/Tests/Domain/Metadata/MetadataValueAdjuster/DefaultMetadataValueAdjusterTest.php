<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataValue;

class DefaultMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    /** @var DefaultMetadataValueAdjuster */
    private $metadataValueAdjuster;

    protected function setUp() {
        $this->metadataValueAdjuster = new DefaultMetadataValueAdjuster();
    }

    public function testDefaultAdjuster() {
        $actual = $expected = new MetadataValue("default adjuster test");
        $this->assertEquals($expected, $actual);
    }
}
