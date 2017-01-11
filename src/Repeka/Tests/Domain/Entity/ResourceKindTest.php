<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\IllegalEntityStateException;

class ResourceKindTest extends \PHPUnit_Framework_TestCase {

    public function testAddingMetadata() {
        $metadata = $this->createMock(Metadata::class);
        $resourceKind = new ResourceKind([]);
        $resourceKind->addMetadata($metadata);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertContains($metadata, $resourceKind->getMetadataList());
    }

    public function testAddingTheSameMetadataTwice() {
        $this->expectException(IllegalEntityStateException::class);
        $metadata = $this->createMock(Metadata::class);
        $resourceKind = new ResourceKind([]);
        $resourceKind->addMetadata($metadata);
        $resourceKind->addMetadata($metadata);
    }

    public function testUpdatesLabel() {
        $resourceKind = new ResourceKind(['PL' => '1']);
        $resourceKind->update(['PL' => '2'], []);
        $this->assertEquals('2', $resourceKind->getLabel()['PL']);
    }

    public function testAddingNewMetadataWithUpdate() {
        $metadata = $this->createMock(Metadata::class);
        $resourceKind = new ResourceKind([]);
        $resourceKind->update([], [$metadata]);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertContains($metadata, $resourceKind->getMetadataList());
    }

    public function testRemovingObsoleteMetadataWithUpdate() {
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getBaseId')->willReturn(1);
        $metadata2->expects($this->any())->method('getBaseId')->willReturn(2);
        $resourceKind = new ResourceKind([]);
        $resourceKind->addMetadata($metadata1);
        $resourceKind->update([], [$metadata2]);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertNotContains($metadata1, $resourceKind->getMetadataList());
        $this->assertContains($metadata2, $resourceKind->getMetadataList());
    }

    public function testUpdatingExistingMetadata() {
        $metadata = $this->createMock(Metadata::class);
        $resourceKind = new ResourceKind([]);
        $metadata->expects($this->once())->method('update');
        $metadata->expects($this->once())->method('updateOrdinalNumber');
        $resourceKind->addMetadata($metadata);
        $resourceKind->update([], [$metadata]);
    }

    public function testUpdatingOrderOfMetadata() {
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getOrdinalNumber')->willReturn(1);
        $metadata2->expects($this->any())->method('getOrdinalNumber')->willReturn(0);
        $resourceKind = new ResourceKind([]);
        $resourceKind->update([], [$metadata1, $metadata2]);
        $this->assertSame($metadata2, $resourceKind->getMetadataList()[0]);
        $this->assertSame($metadata1, $resourceKind->getMetadataList()[1]);
    }
}
