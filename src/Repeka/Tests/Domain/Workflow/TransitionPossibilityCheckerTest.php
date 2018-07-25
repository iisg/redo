<?php
namespace Repeka\Tests\Domain\Workflow;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\UserRepository;
use Repeka\Domain\Workflow\TransitionAssigneeChecker;
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
    /** @var TransitionAssigneeChecker|\PHPUnit_Framework_MockObject_MockObject */
    private $transitionAssigneeChecker;

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
        $this->transitionAssigneeChecker = $this->createMock(TransitionAssigneeChecker::class);
        $this->checker = new TransitionPossibilityChecker($this->transitionAssigneeChecker);
    }

    private function setTransitionTos(array $tos = []): void {
        $this->transition->method('getToIds')->willReturn($tos);
    }

    private function checkWithDefaults(): TransitionPossibilityCheckResult {
        return $this->checker->check($this->resource, ResourceContents::empty(), $this->transition, $this->executor);
    }

    public function testPositiveWhenAssigneeCheckerAllows() {
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(true);
        $result = $this->checkWithDefaults();
        $this->assertTrue($result->isTransitionPossible());
    }

    public function testNegativeWhenAssigneeCheckerDisallows() {
        $result = $this->checkWithDefaults();
        $this->assertFalse($result->isTransitionPossible());
    }

    public function testReturnsMissingMetadataIds() {
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(true);
        $this->workflow->method('getPlaces')->willReturn(
            [
                $this->createWorkflowPlaceMock('p1', [1]),
                $this->createWorkflowPlaceMock('p2', [2, 3]),
            ]
        );
        $this->setTransitionTos(['p1', 'p2']);
        $result = $this->checkWithDefaults();
        $this->assertEquals([1, 2, 3], $result->getMissingMetadataIds());
        $this->assertFalse($result->isTransitionPossible());
    }
}
