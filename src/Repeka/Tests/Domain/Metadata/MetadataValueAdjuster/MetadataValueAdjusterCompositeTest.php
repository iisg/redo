<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Repository\MetadataRepository;

class MetadataValueAdjusterCompositeTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataValueAdjuster */
    private $metadataValueAdjusterComposite;

    protected function setUp() {
        $adjusterA = $this->createMock(MetadataValueAdjuster::class);
        $adjusterA->method('supports')->willReturn(true);
        $adjusterA->method('adjustMetadataValue')->willReturn(new MetadataValue('adjustedByA'));
        $this->metadataValueAdjusterComposite = new MetadataValueAdjusterComposite(
            [$adjusterA],
            $this->createMock(MetadataRepository::class)
        );
    }

    public function testAdjusting() {
        $adjusted = $this->metadataValueAdjusterComposite->adjustMetadataValue(new MetadataValue('test'), MetadataControl::RELATIONSHIP());
        $this->assertEquals(new MetadataValue('adjustedByA'), $adjusted);
    }

    public function testUseDefaultMetadataValueAdjusterIfLackOfControl() {
        $adjusted = $this->metadataValueAdjusterComposite->adjustMetadataValue(new MetadataValue('test'), MetadataControl::TEXT());
        $this->assertEquals(new MetadataValue('adjustedByA'), $adjusted);
    }
}
