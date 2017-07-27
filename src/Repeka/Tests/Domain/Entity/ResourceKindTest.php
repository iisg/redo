<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\MetadataAlreadyPresentException;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testAddingMetadata() {
        $metadata = $this->createMetadataMock(11, 1);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadata);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertContains($metadata, $resourceKind->getMetadataList());
    }

    public function testAddingTheSameMetadataTwice() {
        $this->expectException(MetadataAlreadyPresentException::class);
        $metadata = $this->createMetadataMock(11, 1);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadata);
        $resourceKind->addMetadata($metadata);
    }

    public function testAddingTheSameBaseMetadataTwice() {
        $this->expectException(MetadataAlreadyPresentException::class);
        $metadata1 = $this->createMetadataMock(11, 1);
        $metadata2 = $this->createMetadataMock(12, 1);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadata1);
        $resourceKind->addMetadata($metadata2);
    }

    public function testAddingBaselessMetadata() {
        $this->expectException(InvalidArgumentException::class);
        $metadata1 = $this->createMetadataMock(11);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadata1);
    }

    public function testUpdatesLabel() {
        $resourceKind = new ResourceKind(['PL' => '1'], 'books');
        $resourceKind->update(['PL' => '2'], []);
        $this->assertEquals('2', $resourceKind->getLabel()['PL']);
    }

    public function testAddingNewMetadataWithUpdate() {
        $metadata = $this->createMetadataMock(11, 1);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->update([], [$metadata]);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertContains($metadata, $resourceKind->getMetadataList());
    }

    public function testRemovingObsoleteMetadataWithUpdate() {
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata1->expects($this->any())->method('getBaseId')->willReturn(1);
        $metadata2->expects($this->any())->method('getBaseId')->willReturn(2);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadata1);
        $resourceKind->update([], [$metadata2]);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertNotContains($metadata1, $resourceKind->getMetadataList());
        $this->assertContains($metadata2, $resourceKind->getMetadataList());
    }

    public function testUpdatingExistingMetadata() {
        $metadata = $this->createMetadataMock(11, 1);
        $resourceKind = new ResourceKind([], 'books');
        $metadata->expects($this->once())->method('update');
        $metadata->expects($this->once())->method('updateOrdinalNumber');
        $resourceKind->addMetadata($metadata);
        $resourceKind->update([], [$metadata]);
    }

    public function testUpdatingOrderOfMetadata() {
        $metadata1 = $this->createMetadataMock(10, 1);
        $metadata2 = $this->createMetadataMock(20, 2);
        $metadata1->expects($this->any())->method('getOrdinalNumber')->willReturn(1);
        $metadata2->expects($this->any())->method('getOrdinalNumber')->willReturn(0);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->update([], [$metadata1, $metadata2]);
        $this->assertSame($metadata2, $resourceKind->getMetadataList()[0]);
        $this->assertSame($metadata1, $resourceKind->getMetadataList()[1]);
    }

    public function testSettingResourceClass() {
        $resourceKind = new ResourceKind([], 'books');
        $this->assertEquals('books', $resourceKind->getResourceClass());
    }

    public function testSettingWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resourceKind = new ResourceKind([], 'books', $workflow);
        $this->assertEquals($workflow, $resourceKind->getWorkflow());
    }

    public function testWorkflowIsOptional() {
        $resourceKind = new ResourceKind([], 'books');
        $this->assertNull($resourceKind->getWorkflow());
    }

    public function testFindsMetadataByBase() {
        $base = $this->createMetadataMock(2);
        $resourceKind = new ResourceKind([], 'books');
        foreach ([
                     $this->createMetadataMock(11, 1),
                     $expectedResult = $this->createMetadataMock(12, 2),
                     $this->createMetadataMock(13, 3),
                 ] as $metadata) {
            $resourceKind->addMetadata($metadata);
        }
        $actualResult = $resourceKind->getMetadataByBaseId($base->getId());
        $this->assertEquals($expectedResult, $actualResult);
    }

    public function testThrowsWhenNoMetadataForBaseFound() {
        $this->expectException(\InvalidArgumentException::class);
        $resourceKind = new ResourceKind([], 'books');
        foreach ([
                     $base = $this->createMetadataMock(0),
                     $this->createMetadataMock(11, 1),
                     $this->createMetadataMock(12, 2),
                     $this->createMetadataMock(13, 3),
                 ] as $metadata) {
            $resourceKind->addMetadata($metadata);
        }
        $resourceKind->getMetadataByBaseId($base->getId());
    }

    public function testGettingMetadataByControl() {
        $metadataA1 = $this->createMetadataMock(10, 1, 'A');
        $metadataA2 = $this->createMetadataMock(20, 2, 'A');
        $metadataB = $this->createMetadataMock(30, 3, 'B');
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadataA1);
        $resourceKind->addMetadata($metadataB);
        $resourceKind->addMetadata($metadataA2);
        $metadataWithAControl = $resourceKind->getMetadataByControl('A');
        $this->assertCount(2, $metadataWithAControl);
        $this->assertContains($metadataA1, $metadataWithAControl);
        $this->assertContains($metadataA2, $metadataWithAControl);
    }

    public function testGettingBaseMetadataIds() {
        $metadata1 = $this->createMetadataMock(10, 1);
        $metadata2 = $this->createMetadataMock(20, 2);
        $metadata3 = $this->createMetadataMock(30, 3);
        $resourceKind = new ResourceKind([], 'books');
        $resourceKind->addMetadata($metadata1);
        $resourceKind->addMetadata($metadata2);
        $resourceKind->addMetadata($metadata3);
        $this->assertEquals([1, 2, 3], $resourceKind->getBaseMetadataIds());
    }
}
