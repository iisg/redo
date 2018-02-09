<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Tests\Traits\StubsTrait;

class ResourceEntityTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind|PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;

    protected function setUp() {
        $this->resourceKind = $this->createResourceKindMock();
    }

    public function testSettingResourceKind() {
        $resource = new ResourceEntity($this->resourceKind, [1 => ['AA']]);
        $this->assertSame($this->resourceKind, $resource->getKind());
    }

    public function testSettingResourceClassFromResourceKind() {
        $resource = new ResourceEntity($this->resourceKind, [1 => ['AA']]);
        $this->assertSame($this->resourceKind->getResourceClass(), $resource->getResourceClass());
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
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resource = new ResourceEntity($this->createResourceKindMock(1, 'books', [], $workflow), []);
        $this->assertTrue($resource->hasWorkflow());
        $this->assertSame($workflow, $resource->getWorkflow());
    }

    public function testApplyTransition() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resource = new ResourceEntity($this->createResourceKindMock(1, 'books', [], $workflow), []);
        $workflow->expects($this->once())->method('apply')->with($resource, 't1')->willReturn($resource);
        $resource->applyTransition('t1');
    }

    public function testGettingValues() {
        $rk = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(11, 1), $this->createMetadataMock(12, 2)]);
        $resource = new ResourceEntity($rk, [11 => ['A', 'B']]);
        $this->assertEquals(['A', 'B'], $resource->getValues($this->createMetadataMock(11, 1)));
    }

    public function testGettingValuesOfEmptyMetadata() {
        $rk = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(11, 1), $this->createMetadataMock(12, 2)]);
        $resource = new ResourceEntity($rk, [1 => ['A', 'B']]);
        $this->assertEquals([], $resource->getValues($this->createMetadataMock(12, 2)));
    }

    public function testGettingValuesById() {
        $rk = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(11, 1), $this->createMetadataMock(12, 2)]);
        $resource = new ResourceEntity($rk, [11 => ['A', 'B']]);
        $this->assertEquals(['A', 'B'], $resource->getValues($this->createMetadataMock(11)));
    }

    public function testGettingValuesOfNotExistingMetadata() {
        $this->resourceKind->method('getMetadataList')->willReturn([$this->createMetadataMock(11, 1), $this->createMetadataMock(12, 2)]);
        $resource = new ResourceEntity($this->resourceKind, [1 => ['A', 'B']]);
        $this->expectException(InvalidArgumentException::class);
        $resource->getValues($this->createMetadataMock(13, 3));
    }
}
