<?php
namespace Repeka\Tests\Domain\Entity;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;

class ResourceEntityTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceKind|PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;

    protected function setUp() {
        $this->resourceKind = $this->createMock(ResourceKind::class);
    }

    public function testSettingResourceKind() {
        $resource = new ResourceEntity($this->resourceKind, [1 => ['AA']]);
        $this->assertSame($this->resourceKind, $resource->getKind());
    }

    public function testRemovingEmptyContents() {
        $resource = new ResourceEntity($this->resourceKind, [1 => ['AA']]);
        $resource->updateContents([2 => 'AA']);
        $this->assertEquals([2 => 'AA'], $resource->getContents());
    }

    public function testEmptyMetadataAreRemovedFromContents() {
        $resource = new ResourceEntity($this->resourceKind, []);
        $resource->updateContents([1 => ['AA'], 2 => [], 3 => ['AA']]);
        $this->assertArrayHasKey(1, $resource->getContents());
        $this->assertArrayNotHasKey(2, $resource->getContents());
        $this->assertArrayHasKey(3, $resource->getContents());
    }

    public function testUpdatingMarking() {
        $resource = new ResourceEntity($this->resourceKind, [1 => ['AA']]);
        $marking = ['scanned'];
        $resource->setMarking($marking);
        $this->assertEquals($marking, $resource->getMarking());
    }

    public function testNoMarkingAtTheBeginning() {
        $resource = new ResourceEntity($this->resourceKind, [1 => ['AA']]);
        $this->assertNull($resource->getMarking());
    }

    public function testNoWorkflow() {
        $resource = new ResourceEntity($this->resourceKind, []);
        $this->assertFalse($resource->hasWorkflow());
        $this->assertNull($resource->getWorkflow());
    }

    public function testHasWorkflow() {
        $resource = new ResourceEntity($this->resourceKind, []);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $this->resourceKind->expects($this->any())->method('getWorkflow')->willReturn($workflow);
        $this->assertTrue($resource->hasWorkflow());
        $this->assertSame($workflow, $resource->getWorkflow());
    }

    public function testApplyTransition() {
        $resource = new ResourceEntity($this->resourceKind, []);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $this->resourceKind->expects($this->any())->method('getWorkflow')->willReturn($workflow);
        $workflow->expects($this->once())->method('apply')->with($resource, 't1')->willReturn($resource);
        $resource->applyTransition('t1');
    }
}
