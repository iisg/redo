<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\ResourceWorkflowTransition;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandValidator;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Domain\Workflow\TransitionPossibilityCheckResult;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTransitionCommandValidatorTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceWorkflow|PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var  ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var ResourceKind|PHPUnit_Framework_MockObject_MockObject */
    private $resourceKind;
    /** @var TransitionPossibilityChecker */
    private $transitionPossibilityChecker;

    /** @var ResourceTransitionCommandValidator */
    private $validator;

    protected function setUp() {
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->transitionPossibilityChecker = $this->createMock(TransitionPossibilityChecker::class);
        $this->transitionPossibilityChecker->method('check')->willReturn(new TransitionPossibilityCheckResult([], false, false));
        $this->validator = new ResourceTransitionCommandValidator($this->transitionPossibilityChecker);
        $this->resource->expects($this->any())->method('getWorkflow')->willReturn($this->workflow);
        $this->resourceKind = $this->createMock(ResourceKind::class);
        $this->resource->method('getKind')->willReturn($this->resourceKind);
    }

    public function testValid() {
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $this->workflow->method('getPlaces')->willReturn([
            $this->createWorkflowPlaceMock('p1', [1]),
            $this->createWorkflowPlaceMock('p2', []),
        ]);
        $this->configureTransition('t1', true, ['p2']);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenEmptyTransitionId() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/blank/');
        $this->resource->method('getId')->willReturn(1);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $command = new ResourceTransitionCommand($this->resource, '', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenNotSavedResourceKind() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/greater than 0/');
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenNoWorkflow() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/workflow/');
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(false);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenInvalidTransition() {
        $this->expectException(InvalidCommandException::class);
        $this->expectExceptionMessageRegExp('/transitionId/');
        $this->resource->method('getId')->willReturn(1);
        $this->resource->method('hasWorkflow')->willReturn(true);
        $this->workflow->expects($this->once())->method('getTransitions')
            ->willReturn([new ResourceWorkflowTransition([], [], [], [], 't1')]);
        $command = new ResourceTransitionCommand($this->resource, 't2', $this->createMock(User::class));
        $this->validator->validate($command);
    }

    public function testInvalidWhenTransitionImpossible() {
        $this->expectException(InvalidCommandException::class);
        $this->resource->expects($this->once())->method('getId')->willReturn(1);
        $this->resource->expects($this->once())->method('hasWorkflow')->willReturn(true);
        $transitionPossibilityChecker = $this->createMock(TransitionPossibilityChecker::class);
        $transitionPossibilityChecker->method('check')->willReturn(new TransitionPossibilityCheckResult([], true, true));
        $validator = new ResourceTransitionCommandValidator($transitionPossibilityChecker);
        $this->configureTransition('t1', false);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->createMock(User::class));
        $validator->validate($command);
    }

    private function configureTransition(string $id, bool $userHasRole, array $tos = []): void {
        $transition = $this->createMock(ResourceWorkflowTransition::class);
        $transition->method('getId')->willReturn($id);
        $transition->method('userHasRoleRequiredToApply')->willReturn($userHasRole);
        $transition->method('getToIds')->willReturn($tos);
        $this->workflow->method('getTransitions')->willReturn([$transition]);
        $this->workflow->method('getTransition')->willReturn($transition);
    }
}
