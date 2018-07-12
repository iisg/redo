<?php
namespace Repeka\Tests\Domain\Workflow;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Workflow\TransitionAssigneeChecker;
use Repeka\Tests\Traits\StubsTrait;

class TransitionAssigneeCheckerTest extends \PHPUnit_Framework_TestCase {
    private $transition;
    use StubsTrait;

    /** @var TransitionAssigneeChecker */
    private $checker;
    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    private $executor;

    protected function setUp() {
        $this->checker = new TransitionAssigneeChecker();
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->transition = $this->createWorkflowTransitionMock([], [], ['p']);
        $this->executor = $this->createMockEntity(User::class, 1);
        $this->executor->method('getUserData')->willReturn($this->createResourceMock(88));
    }

    public function testGetAssigneeMetadataIds() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1])]);
        $metadataIds = $this->checker->getAssigneeMetadataIds($this->workflow, $this->transition);
        $this->assertEquals([1], $metadataIds);
    }

    public function testGetAssigneeMetadataIdsFromManyPlaces() {
        $transition = $this->createWorkflowTransitionMock([], [], ['p1', 'p2']);
        $this->workflow->method('getPlaces')->willReturn(
            [$this->createWorkflowPlaceMock('p1', [], [1, 3]), $this->createWorkflowPlaceMock('p2', [], [1, 2])]
        );
        $metadataIds = $this->checker->getAssigneeMetadataIds($this->workflow, $transition);
        $this->assertEquals([1, 3, 2], $metadataIds);
    }

    public function testGetAutoAssignMetadataIds() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [], [1])]);
        $metadataIds = $this->checker->getAutoAssignMetadataIds($this->workflow, $this->transition);
        $this->assertEquals([1], $metadataIds);
    }

    public function testGetAutoAssignMetadataIdsFromManyPlaces() {
        $transition = $this->createWorkflowTransitionMock([], [], ['p1', 'p2']);
        $this->workflow->method('getPlaces')->willReturn(
            [$this->createWorkflowPlaceMock('p1', [], [4], [1, 3]), $this->createWorkflowPlaceMock('p2', [], [], [1, 2])]
        );
        $metadataIds = $this->checker->getAutoAssignMetadataIds($this->workflow, $transition);
        $this->assertEquals([1, 3, 2], $metadataIds);
    }

    public function testGetUserIdsAssignedToTransition() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1])]);
        $resource = $this->createResourceMockWithContents([1 => 42]);
        $userIds = $this->checker->getUserIdsAssignedToTransition($resource, $this->transition);
        $this->assertEquals([42], $userIds);
    }

    public function testGetUserIdsAssignedToTransitionIgnoresDuplicatedIds() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1, 2])]);
        $resource = $this->createResourceMockWithContents([1 => 42, 2 => 42]);
        $userIds = $this->checker->getUserIdsAssignedToTransition($resource, $this->transition);
        $this->assertEquals([42], $userIds);
    }

    public function testGetUserIdsAssignedToTransitionReturnsAlsoAutoAssigned() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1], [2])]);
        $resource = $this->createResourceMockWithContents([1 => 42, 2 => 48]);
        $userIds = $this->checker->getUserIdsAssignedToTransition($resource, $this->transition);
        $this->assertEquals([42, 48], $userIds);
    }

    public function testGetUserIdsAssignedToTransitionIgnoresMetadataNotPresentInResourceKind() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1, 42], [2])]);
        $resource = $this->createResourceMockWithContents([1 => 42, 2 => 48, 42 => 52]);
        $userIds = $this->checker->getUserIdsAssignedToTransition($resource, $this->transition);
        $this->assertEquals([42, 48], $userIds);
    }

    public function testGetUserIdsAssignedToTransitionReturnsEmptyListIfNoAssigneesInResource() {
        $transition = $this->createWorkflowTransitionMock([], [], ['p']);
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1])]);
        $resource = $this->createResourceMockWithContents([]);
        $userIds = $this->checker->getUserIdsAssignedToTransition($resource, $transition);
        $this->assertEquals([], $userIds);
    }

    public function testGetUserIdsAssignedToTransitionReturnsEmptyListIfNoAssigneesInPlace() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p')]);
        $resource = $this->createResourceMockWithContents([]);
        $userIds = $this->checker->getUserIdsAssignedToTransition($resource, $this->transition);
        $this->assertEquals([], $userIds);
    }

    public function testCanApplyTransitionWhenNoAssigneesInPlace() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p')]);
        $resource = $this->createResourceMockWithContents([]);
        $this->assertTrue($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCantApplyTransitionWhenNoAssigneesInResource() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1])]);
        $resource = $this->createResourceMockWithContents([]);
        $this->assertFalse($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCanApplyTransitionWhenExecutorIsAssignee() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1])]);
        $resource = $this->createResourceMockWithContents([1 => 88]);
        $this->assertTrue($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCantApplyTransitionWhenExecutorIsNotAssignee() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1])]);
        $resource = $this->createResourceMockWithContents([1 => 98]);
        $this->assertFalse($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCanApplyTransitionWhenExecutorIsAutoAssign() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [], [1])]);
        $resource = $this->createResourceMockWithContents([1 => 88]);
        $this->assertTrue($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCantApplyTransitionWhenOtherUserIsAutoAssigned() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [], [1])]);
        $resource = $this->createResourceMockWithContents([1 => 89]);
        $this->assertFalse($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCanApplyTransitionWhenExecutorIsAutoAssignButNotAssignee() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [2], [1])]);
        $resource = $this->createResourceMockWithContents([1 => 88, 2 => 89]);
        $this->assertTrue($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCanApplyTransitionWhenExecutorIsAssigneeButNoAutoAssign() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [1], [2])]);
        $resource = $this->createResourceMockWithContents([1 => 88, 2 => 89]);
        $this->assertTrue($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCanApplyTransitionIfWillBeAutoAssigned() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [], [1])]);
        $resource = $this->createResourceMockWithContents([]);
        $this->assertTrue($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    public function testCanApplyTransitionIfWillBeAutoAssignedInTheFirstPlace() {
        $resource = $this->createResourceMockWithContents([]);
        $this->workflow->method('getInitialPlace')->willReturn($this->createWorkflowPlaceMock('p', [], [], [1]));
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [], [1])]);
        $transition = SystemTransition::CREATE()->toTransition($resource->getKind());
        $this->assertTrue($this->checker->canApplyTransition($resource, $transition, $this->executor));
    }

    public function testCantApplyTransitionIfWillBeAutoAssignedButThereIsAnAssignee() {
        $this->workflow->method('getPlaces')->willReturn([$this->createWorkflowPlaceMock('p', [], [2], [1])]);
        $resource = $this->createResourceMockWithContents([]);
        $this->assertFalse($this->checker->canApplyTransition($resource, $this->transition, $this->executor));
    }

    private function createResourceMockWithContents(array $contents) {
        return $this->createResourceMock(
            1,
            $this->createResourceKindMock(
                2,
                'books',
                [$this->createMetadataMock(1), $this->createMetadataMock(2), $this->createMetadataMock(3)],
                $this->workflow
            ),
            $contents
        );
    }
}
