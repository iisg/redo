<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;

class RelationshipMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var RelationshipMetadataValueAdjuster */
    private $metadataValueAdjuster;

    protected function setUp() {
        $this->metadataValueAdjuster = new RelationshipMetadataValueAdjuster();
    }

    public function testConvertResourceToResourceId() {
        $resource = $this->createResourceMock(1);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::RELATIONSHIP());
        $actualValue = $this->metadataValueAdjuster->adjustMetadataValue(new MetadataValue($resource), $metadata);
        $this->assertEquals(new MetadataValue(1), $actualValue);
    }
}
