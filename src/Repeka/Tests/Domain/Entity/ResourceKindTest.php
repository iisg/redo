<?php
namespace Repeka\Tests\Domain\Entity;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testUpdatesLabel() {
        $resourceKind = new ResourceKind('', ['PL' => '1'], [$this->createMetadataMock(1)]);
        $resourceKind->update(['PL' => '2'], []);
        $this->assertEquals('2', $resourceKind->getLabel()['PL']);
    }

    public function testRemovingObsoleteMetadataWithUpdate() {
        $metadata1 = $this->createMetadataMock(1);
        $metadata2 = $this->createMetadataMock(2);
        $resourceKind = new ResourceKind('', [], [$metadata1]);
        $resourceKind->setMetadataList([$metadata2]);
        $this->assertCount(1, $resourceKind->getMetadataList());
        $this->assertNotContains($metadata1, $resourceKind->getMetadataList());
        $this->assertContains($metadata2, $resourceKind->getMetadataList());
    }

    public function testSettingResourceClassBasedOnTheFirstMetadata() {
        $resourceKind = new ResourceKind('', [], [$this->createMetadataMock(1)]);
        $this->assertEquals('books', $resourceKind->getResourceClass());
    }

    public function testSettingResourceClassBasedOnTheFirstNonSystemMetadata() {
        $resourceKind = new ResourceKind('', [], [SystemMetadata::PARENT()->toMetadata(), $this->createMetadataMock(1)]);
        $this->assertEquals('books', $resourceKind->getResourceClass());
    }

    public function testCannotCreateResourceKindWithoutMetadata() {
        $this->expectException(\InvalidArgumentException::class);
        new ResourceKind('', [], []);
    }

    public function testCannotCreateResourceWithOnlyParentMetadata() {
        $this->expectException(\InvalidArgumentException::class);
        new ResourceKind('', [], [SystemMetadata::PARENT()->toMetadata()]);
    }

    public function testSettingWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resourceKind = new ResourceKind('', [], [$this->createMetadataMock(1)], $workflow);
        $this->assertEquals($workflow, $resourceKind->getWorkflow());
    }

    public function testWorkflowIsOptional() {
        $resourceKind = new ResourceKind('', [], [$this->createMetadataMock(1)]);
        $this->assertNull($resourceKind->getWorkflow());
    }

    public function testFindsMetadataById() {
        $metadata1 = $this->createMetadataMock(1);
        $metadata2 = $this->createMetadataMock(2);
        $resourceKind = new ResourceKind('', [], [$metadata1, $metadata2]);
        $this->assertSame($metadata1, $resourceKind->getMetadataById(1));
        $this->assertSame($metadata2, $resourceKind->getMetadataById(2));
    }

    public function testThrowsWhenNoMetadataForIdFound() {
        $this->expectException(\InvalidArgumentException::class);
        $resourceKind = new ResourceKind('', [], [$this->createMetadataMock(1)]);
        $resourceKind->getMetadataById(2);
    }

    public function testGettingMetadataByControl() {
        $metadataA1 = $this->createMetadataMock(10, 1, MetadataControl::TEXTAREA());
        $metadataA2 = $this->createMetadataMock(20, 2, MetadataControl::TEXTAREA());
        $metadataB = $this->createMetadataMock(30, 3, MetadataControl::BOOLEAN());
        $resourceKind = new ResourceKind('', [], [$metadataA1, $metadataB, $metadataA2]);
        $metadataWithAControl = $resourceKind->getMetadataByControl(MetadataControl::TEXTAREA());
        $this->assertCount(2, $metadataWithAControl);
        $this->assertContains($metadataA1, $metadataWithAControl);
        $this->assertContains($metadataA2, $metadataWithAControl);
    }

    public function testGettingMetadataIds() {
        $metadata1 = $this->createMetadataMock(10);
        $metadata2 = $this->createMetadataMock(20);
        $metadata3 = $this->createMetadataMock(30);
        $resourceKind = new ResourceKind('', [], [$metadata1, $metadata2, $metadata3]);
        $this->assertEquals([10, 20, 30], $resourceKind->getMetadataIds());
    }
}
