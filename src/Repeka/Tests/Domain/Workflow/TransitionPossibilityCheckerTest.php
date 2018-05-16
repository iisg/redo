<?php
namespace Repeka\Tests\Domain\Workflow;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Repeka\Tests\Traits\StubsTrait;

class TransitionPossibilityCheckerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var User|\PHPUnit_Framework_MockObject_MockObject */
    private $executor;
    /** @var ResourceKind|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;
    /** @var  ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var ResourceWorkflowTransition|\PHPUnit_Framework_MockObject_MockObject */
    private $transition;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /** @var TransitionPossibilityChecker */
    private $checker;
    /** @var UserRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $userRepository;

    protected function setUp() {
        $this->executor = $this->createMock(User::class);
        $this->executor->method('getId')->willReturn(10203040);  // arbitrary unusual number
        $this->executor->method('getUserData')->willReturn($this->createResourceMock($this->executor->getId()));
        $this->transition = $this->createMock(ResourceWorkflowTransition::class);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->workflow->method('getTransitions')->willReturn([$this->transition]);
        $this->workflow->method('getTransition')->willReturn($this->transition);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resource->method('getKind')->willReturn($this->resourceKind);
        $this->resource->method('getWorkflow')->willReturn($this->workflow);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->checker = new TransitionPossibilityChecker($this->userRepository);
    }

    private function configureTransition(bool $userHasRole, array $tos = []): void {
        $this->transition->method('userHasRoleRequiredToApply')->willReturn($userHasRole);
        $this->transition->method('getToIds')->willReturn($tos);
    }

    private function checkWithDefaults(): TransitionPossibilityCheckResult {
        return $this->checker->check($this->resource, ResourceContents::empty(), $this->transition, $this->executor);
    }

    public function testPositiveWithNoAssignees() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1'),
                $this->createWorkflowPlaceMock('p2'),
            ]
        );
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertTrue($result->isTransitionPossible());
    }

    public function testPositiveWithAssignees() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [], [1]),
                $this->createWorkflowPlaceMock('p2', [], [2]),
            ]
        );
        $resourceContents = ResourceContents::fromArray(
            [
                1 => [1000],
                2 => [2000, ['value' => $this->executor->getId()]],
            ]
        );
        $this->resource->method('getContents')->willReturn($resourceContents);
        $this->resourceKind->method('getMetadataIds')->willReturn([1, 2]);
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertTrue($result->isTransitionPossible());
        $this->assertFalse($result->isOtherUserAssigned());
    }

    public function testPositiveWithUserGroupAssignee() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [], [1]),
                $this->createWorkflowPlaceMock('p2', [], [2]),
            ]
        );
        $this->resource->method('getContents')->willReturn(
            ResourceContents::fromArray(
                [
                    1 => [1000],
                    2 => [2000],
                ]
            )
        );
        $this->userRepository->method('findUserGroups')->with($this->executor)->willReturn([$this->createResourceMock(1000)]);
        $this->resourceKind->method('getMetadataIds')->willReturn([1, 2]);
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertTrue($result->isTransitionPossible());
        $this->assertFalse($result->isOtherUserAssigned());
    }

    public function testToleratesMissingAssigneeMetadata() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [], [1]),
                $this->createWorkflowPlaceMock('p2', [], [2]),
            ]
        );
        $resourceContents = ResourceContents::fromArray(
            [
                1 => [1000],
            ]
        );
        $this->resource->method('getContents')->willReturn($resourceContents);
        $this->resourceKind->method('getMetadataIds')->willReturn([1, 2]);
        $this->configureTransition(true, ['p1', 'p2']);
        $this->checkWithDefaults();
    }

    public function testNegativeWhenNotAssignee() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [], [1]),
                $this->createWorkflowPlaceMock('p2', [], [3]),
            ]
        );
        $resourceContents = ResourceContents::fromArray(
            [
                1 => [1000],
                2 => [2000, $this->executor->getId()],
            ]
        );
        $this->resource->method('getContents')->willReturn($resourceContents);
        $this->resourceKind->method('getMetadataIds')->willReturn([3]);
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertFalse($result->isTransitionPossible());
        $this->assertTrue($result->isOtherUserAssigned());
    }

    public function testPositiveWhenAssigneeMetadataNotInResourceKind() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [], [1]),
                $this->createWorkflowPlaceMock('p2', [], [35656]), // resource kind does not have metadata 35656 (arbitrary id)
            ]
        );
        $resourceContents = ResourceContents::fromArray(
            [
                1 => [1000],
                2 => [2000, $this->executor->getId()],
            ]
        );
        $this->resource->method('getContents')->willReturn($resourceContents);
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertTrue($result->isTransitionPossible());
        $this->assertFalse($result->isOtherUserAssigned());
    }

    public function testNegativeWhenExecutorIsMissingRole() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1'),
                $this->createWorkflowPlaceMock('p2'),
            ]
        );
        $this->configureTransition(false, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertFalse($result->isTransitionPossible());
        $this->assertTrue($result->isUserMissingRequiredRole());
    }

    public function testReturnsMissingMetadataIds() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [1]),
                $this->createWorkflowPlaceMock('p2', [2, 3]),
            ]
        );
        $this->resourceKind->method('getMetadataIds')->willReturn([2, 3]);
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertEquals([2, 3], $result->getMissingMetadataIds());
        $this->assertFalse($result->isTransitionPossible());
    }

    public function testDoesntReturnMissingMetadataIdsNotInResourceKind() {
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [1]),
                $this->createWorkflowPlaceMock('p2', [2, 3, 3784, 348753]),
            ]
        );
        $this->resourceKind->method('getMetadataIds')->willReturn([2, 3]);
        $this->configureTransition(true, ['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertEquals([2, 3], $result->getMissingMetadataIds());
        $this->assertFalse($result->isTransitionPossible());
    }

    public function testPositiveWhenResourceHasNoWorkflow() {
        $resourceKind = $this->createMock(ResourceKind::class);
        $resource = $this->createMock(ResourceEntity::class);
        $resource->method('hasWorkflow')->willReturn(false);
        $result = $this->checker->check(
            $resource,
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($resourceKind, $this->resource),
            $this->executor
        );
        $this->assertTrue($result->isTransitionPossible());
    }

    public function testPositiveWhenResourceHasWorkflowAndSystemTransition() {
        $result = $this->checker->check(
            $this->resource,
            ResourceContents::empty(),
            SystemTransition::UPDATE()->toTransition($this->resourceKind, $this->resource),
            $this->executor
        );
        $this->assertTrue($result->isTransitionPossible());
    }
}
