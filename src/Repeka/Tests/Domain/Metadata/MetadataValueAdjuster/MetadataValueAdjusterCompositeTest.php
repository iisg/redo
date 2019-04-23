<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Tests\Traits\StubsTrait;

class MetadataValueAdjusterCompositeTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

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
        $metadata = $this->createMetadataMock(1, null, MetadataControl::RELATIONSHIP());
        $adjusted = $this->metadataValueAdjusterComposite->adjustMetadataValue(new MetadataValue('test'), $metadata);
        $this->assertEquals(new MetadataValue('adjustedByA'), $adjusted);
    }

    public function testUseDefaultMetadataValueAdjusterIfLackOfControl() {
        $metadata = $this->createMetadataMock(1, null, MetadataControl::TEXT());
        $adjusted = $this->metadataValueAdjusterComposite->adjustMetadataValue(new MetadataValue('test'), $metadata);
        $this->assertEquals(new MetadataValue('adjustedByA'), $adjusted);
    }
}
