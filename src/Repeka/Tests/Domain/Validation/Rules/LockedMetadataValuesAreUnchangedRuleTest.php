<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Assert\AssertionFailedException;
use Repeka\Domain\Entity\Identifiable;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
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
        $this->assertTrue($this->rule->forResourceAndTransition($resource)->validate([]));
    }

    public function testAcceptsForNoLockedMetadata() {
        $place = new ResourceWorkflowPlace([], 'place', [], []);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->expects($this->once())->method('getPlaces')->willReturn([$place, $place]);
        $this->resource->method('getContents')->willReturn(ResourceContents::fromArray([0 => ['foo'], 1 => ['bar']]));
        $newContents = ResourceContents::fromArray([2 => ['baz'], 3 => ['quux']]);
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testAcceptsUnchangedLockedMetadata() {
        $place = new ResourceWorkflowPlace([], 'place', [], [1]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testAcceptsUnchangedLockedMetadataAndSubmetadata() {
        $place = new ResourceWorkflowPlace([], 'place', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'qux']]]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'qux']]]]);
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testRejectsChangedLockedMetadata() {
        $place = new ResourceWorkflowPlace([], 'place', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testRejectsChangedSubmetadataOfLockedMetadata() {
        $place = new ResourceWorkflowPlace([], 'place', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'qux']]]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [['value' => 'baz', 'submetadata' => [3 => 'wux']]]]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testAcceptsUnchangedLockedMetadataForMultiplePlaces() {
        $placeFrom = new ResourceWorkflowPlace([], 'from', [], []);
        $placeTo1 = new ResourceWorkflowPlace([], 'to1', [], [1]);
        $placeTo2 = new ResourceWorkflowPlace([], 'to2', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['from'], ['to1', 'to2']);
        $this->workflow->method('getPlaces')->willReturn([$placeFrom, $placeTo1, $placeTo2]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testRejectsChangedLockedMetadataForMultiplePlaces() {
        $placeFrom = new ResourceWorkflowPlace([], 'from', [], []);
        $placeTo1 = new ResourceWorkflowPlace([], 'to1', [], [1]);
        $placeTo2 = new ResourceWorkflowPlace([], 'to2', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['from'], ['to1', 'to2']);
        $this->workflow->method('getPlaces')->willReturn([$placeFrom, $placeTo1, $placeTo2]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testRejectsWithNiceErrorMessage() {
        $this->expectExceptionMessage("Metadata 1, 2");
        $place = new ResourceWorkflowPlace([], 'place', [], [1, 2]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar', 'ni'], 2 => ['quux']]);
        $this->rule->forResourceAndTransition($this->resource, $transition)->assert($newContents);
    }

    public function testObjectsAreEqualToTheirIds() {
        /** @var Identifiable|\PHPUnit_Framework_MockObject_MockObject $identifiable */
        $identifiable = $this->createMock(Identifiable::class);
        $identifiable->method('getId')->willReturn(123);
        $placeFrom = new ResourceWorkflowPlace([], 'from', [], []);
        $placeTo = new ResourceWorkflowPlace([], 'to', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['from'], ['to']);
        $this->workflow->method('getPlaces')->willReturn([$placeFrom, $placeTo]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable->getId()]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable]]);
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testRejectsWhenObjectsAreNotEqualToTheirIds() {
        /** @var Identifiable|\PHPUnit_Framework_MockObject_MockObject $identifiable */
        $identifiable = $this->createMock(Identifiable::class);
        $identifiable->method('getId')->willReturn(123);
        $place1 = new ResourceWorkflowPlace([], 'from', [], [1]);
        $place2 = new ResourceWorkflowPlace([], 'to', [], [2]);
        $transition = new ResourceWorkflowTransition([], ['from'], ['to']);
        $this->workflow->method('getPlaces')->willReturn([$place1, $place2]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable->getId() + 1]]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => [$identifiable]]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testObjectsAreEqualToTheirIdsInSubmetadata() {
        /** @var Identifiable|\PHPUnit_Framework_MockObject_MockObject $identifiable */
        $identifiable = $this->createMock(Identifiable::class);
        $identifiable->method('getId')->willReturn(123);
        $placeFrom = new ResourceWorkflowPlace([], 'from', [], []);
        $placeTo = new ResourceWorkflowPlace([], 'to', [], [1, 2]);
        $transition = new ResourceWorkflowTransition([], ['from'], ['to']);
        $this->workflow->method('getPlaces')->willReturn([$placeFrom, $placeTo]);
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
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testConsidersAssigneeMetadataLocked() {
        $place = new ResourceWorkflowPlace([], 'place', [], [1], [2]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }

    public function testUsesLockedMetadataOnlyFromNextPlace() {
        $placeFrom = new ResourceWorkflowPlace([], 'from', [], [1]);
        $placeTo = new ResourceWorkflowPlace([], 'to', [], [2]);
        $placeThird = new ResourceWorkflowPlace([], 'not_used', [], [1, 2, 3]);
        $transition = new ResourceWorkflowTransition([], ['from'], ['to']);
        $transitionReversed = new ResourceWorkflowTransition([], ['to'], ['from']);
        $this->workflow->method('getPlaces')->willReturn([$placeFrom, $placeTo, $placeThird]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz'], 3 => ['qwe']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux'], 3 => ['rty']]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
        $this->assertTrue($this->rule->forResourceAndTransition($this->resource, $transitionReversed)->validate($newContents));
    }

    public function testConsidersAutoAssignMetadataLocked() {
        $place = new ResourceWorkflowPlace([], 'place', [], [1], [], [2]);
        $transition = new ResourceWorkflowTransition([], ['place'], ['place']);
        $this->workflow->method('getPlaces')->willReturn([$place]);
        $oldContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['baz']]);
        $this->resource->method('getContents')->willReturn($oldContents);
        $newContents = ResourceContents::fromArray([1 => ['foo', 'bar'], 2 => ['quux']]);
        $this->assertFalse($this->rule->forResourceAndTransition($this->resource, $transition)->validate($newContents));
    }
}
