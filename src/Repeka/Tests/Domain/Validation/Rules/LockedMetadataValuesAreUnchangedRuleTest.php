<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\AssertionFailedException;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Validation\Rules\LockedMetadataValuesAreUnchangedRule;

class LockedMetadataValuesAreUnchangedRuleTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var LockedMetadataValuesAreUnchangedRule */
    private $rule;

    protected function setUp() {
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->method('getWorkflow')->willReturn($this->workflow);
        $this->rule = new LockedMetadataValuesAreUnchangedRule();
    }

    public function testFailsWithoutResource() {
        $this->expectException(AssertionFailedException::class);
        $this->rule->validate([]);
    }

    public function testAcceptsNoWorkflow() {
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('getWorkflow')->willReturn(null);
        $this->assertTrue($this->rule->forResource($resource)->validate([]));
    }

    public function testAcceptsForNoLockedMetadata() {
        $place = new ResourceWorkflowPlace([]);
        $this->workflow->expects($this->once())->method('getPlaces')->willReturn([$place, $place, $place]);
        $this->resource->method('getContents')->willReturn(ResourceContents::fromArray([0 => ['foo'], 1 => ['bar']]));
        $newContents = ResourceContents::fromArray([2 => ['baz'], 3 => ['quux']]);
        $this->assertTrue($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testAcceptsUnchangedLockedMetadata() {
        $place = new ResourceWorkflowPlace([], null, [], [1]);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertTrue($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testAcceptsUnchangedLockedMetadataAndSubmetadata() {
        $place = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'qux']]]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'qux']]]]);
        $this->assertTrue($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testRejectsChangedLockedMetadata() {
        $place = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testRejectsChangedSubmetadataOfLockedMetadata() {
        $place = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'qux']]]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'wux']]]]);
        $this->assertFalse($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testRejectsChangedLockedMetadataForMultiplePlaces() {
        $place1 = new ResourceWorkflowPlace([], null, [], [1]);
        $place2 = new ResourceWorkflowPlace([], null, [], []);
        $place3 = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place1, $place2, $place3]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testRejectsWithNiceErrorMessage() {
        $this->expectExceptionMessage("Metadata 1, 2");
        $place = new ResourceWorkflowPlace([], null, [], [1, 2]);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar', 'ni'], 2 => ['quux']]);
        $this->rule->forResource($this->resource)->assert($newContents);
    }

    public function testObjectsAreEqualToTheirIds() {
        /** @var Identifiable|\PHPUnit_Framework_MockObject_MockObject $identifiable */
        $identifiable = $this->createMock(Identifiable::class);
        $identifiable->method('getId')->willReturn(123);
        $place1 = new ResourceWorkflowPlace([], null, [], [1]);
        $place2 = new ResourceWorkflowPlace([], null, [], []);
        $place3 = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place1, $place2, $place3]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable->getId()]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable]]);
        $this->assertTrue($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testRejectsWhenObjectsAreNotEqualToTheirIds() {
        /** @var Identifiable|\PHPUnit_Framework_MockObject_MockObject $identifiable */
        $identifiable = $this->createMock(Identifiable::class);
        $identifiable->method('getId')->willReturn(123);
        $place1 = new ResourceWorkflowPlace([], null, [], [1]);
        $place2 = new ResourceWorkflowPlace([], null, [], []);
        $place3 = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place1, $place2, $place3]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable->getId() + 1]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable]]);
        $this->assertFalse($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testObjectsAreEqualToTheirIdsInSubmetadata() {
        /** @var Identifiable|\PHPUnit_Framework_MockObject_MockObject $identifiable */
        $identifiable = $this->createMock(Identifiable::class);
        $identifiable->method('getId')->willReturn(123);
        $place1 = new ResourceWorkflowPlace([], null, [], [1]);
        $place2 = new ResourceWorkflowPlace([], null, [], []);
        $place3 = new ResourceWorkflowPlace([], null, [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place1, $place2, $place3]);
        $oldContents = ResourceContents::fromArray(
            [
                1 => ['foo', 'bar'],
                2 => [['value' => $identifiable->getId(), 'submetadata' => [3 => $identifiable->getId()]]],
            ]
        );
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray(
            [
                1 => ['foo', 'bar'],
                2 => [['value' => $identifiable->getId(), 'submetadata' => [3 => $identifiable]]],
            ]
        );
        $this->assertTrue($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testConsidersAssigneeMetadataLocked() {
        $place1 = new ResourceWorkflowPlace([], null, [], [1], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place1]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResource($this->resource)->validate($newContents));
    }

    public function testConsidersAutoAssignMetadataLocked() {
        $place1 = new ResourceWorkflowPlace([], null, [], [1], [], [2]);
        $this->workflow->method('getPlaces')->willReturn([$place1]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResource($this->resource)->validate($newContents));
    }
}
