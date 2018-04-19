<?php
namespace Repeka\Tests\Domain\Entity;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceEntityTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceKind|PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;

    protected function setUp() {
        $this->resourceKind = $this->createResourceKindMock();
    }

    public function testSettingResourceKind() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::fromArray([1 => ['AA']]));
        $this->assertSame($this->resourceKind, $resource->getKind());
    }

    public function testSettingResourceClassFromResourceKind() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::fromArray([1 => ['AA']]));
        $this->assertSame($this->resourceKind->getResourceClass(), $resource->getResourceClass());
    }

    public function testRemovingEmptyContents() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::fromArray([1 => ['AA']]));
        $resource->updateContents(ResourceContents::fromArray([2 => 'AA']));
        $this->assertEquals(ResourceContents::fromArray([2 => 'AA']), $resource->getContents());
    }

    public function testEmptyMetadataAreRemovedFromContents() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::empty());
        $resource->updateContents(ResourceContents::fromArray([1 => ['AA'], 2 => [], 3 => ['AA']]));
        $this->assertArrayHasKey(1, $resource->getContents());
        $this->assertArrayNotHasKey(2, $resource->getContents());
        $this->assertArrayHasKey(3, $resource->getContents());
    }

    public function testUpdatingMarking() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::fromArray([1 => ['AA']]));
        $marking = ['scanned'];
        $resource->setMarking($marking);
        $this->assertEquals($marking, $resource->getMarking());
    }

    public function testNoMarkingAtTheBeginning() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::fromArray([1 => ['AA']]));
        $this->assertNull($resource->getMarking());
    }

    public function testNoWorkflow() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::empty());
        $this->assertFalse($resource->hasWorkflow());
        $this->assertNull($resource->getWorkflow());
    }

    public function testHasWorkflow() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resource = new ResourceEntity($this->createResourceKindMock(1, 'books', [], $workflow), ResourceContents::empty());
        $this->assertTrue($resource->hasWorkflow());
        $this->assertSame($workflow, $resource->getWorkflow());
    }

    public function testApplyTransition() {
        $workflow = $this->createMock(ResourceWorkflow::class);
        $resource = new ResourceEntity($this->createResourceKindMock(1, 'books', [], $workflow), ResourceContents::empty());
        $workflow->expects($this->once())->method('apply')->with($resource, 't1')->willReturn($resource);
        $resource->applyTransition('t1');
    }

    public function testGettingValues() {
        $rk = $this->createResourceKindMock(1, 'books', [$this->createMetadataMock(11, 1), $this->createMetadataMock(12, 2)]);
        $resource = new ResourceEntity($rk, ResourceContents::fromArray([11 => ['A', 'B']]));
        $this->assertEquals(['A', 'B'], $resource->getContents()->getValues(11));
    }

    public function testGettingAuditData() {
        $resource = new ResourceEntity($this->resourceKind, ResourceContents::fromArray([1 => ['AA']]));
        EntityUtils::forceSetId($resource, 2);
        $auditData = $resource->getAuditData();
        $this->assertEquals(
            [
                'resource' => [
                    'id' => 2,
                    'kindId' => 1,
                    'contents' => ResourceContents::fromArray([1 => ['AA']])->toArray(),
                    'resourceClass' => 'books',
                    'places' => [],
                ],
            ],
            $auditData
        );
    }
}
