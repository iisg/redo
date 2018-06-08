<?php
namespace Repeka\Tests\Domain\UseCase\Assignment;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Assignment\TaskListQuery;
use Repeka\Domain\UseCase\Assignment\TaskListQueryHandler;
use Repeka\Domain\UseCase\Assignment\TasksCollection;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Tests\Traits\StubsTrait;

class TaskListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceRepository */
    private $resourceRepository;
    /** @var TaskListQueryHandler */
    private $handler;
    /** @var TransitionPossibilityChecker|PHPUnit_Framework_MockObject_MockObject */
    private $transitionPossibilityChecker;
    /** @var ResourceWorkflow|PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var User|PHPUnit_Framework_MockObject_MockObject */
    private $user;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->transitionPossibilityChecker = $this->createMock(TransitionPossibilityChecker::class);
        $this->handler = new TaskListQueryHandler($this->resourceRepository, $this->transitionPossibilityChecker);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createResourceMock(1, $this->createResourceKindMock(1, 'books', [], $this->workflow));
        $this->user = $this->createMock(User::class);
    }

    public function testGettingTasksWhenAssignee() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionPossibilityChecker->method('isTransitionGuardedByAssignees')->willReturn(true);
        $this->transitionPossibilityChecker->method('executorIsAssignee')->willReturn(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [$this->resource])], $result);
    }

    public function testGettingTasksWhenNoOneAssigned() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionPossibilityChecker->method('isTransitionGuardedByAssignees')->willReturn(false);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEmpty($result);
    }

    public function testGettingTasksWhenOtherUserIsAssignee() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionPossibilityChecker->method('isTransitionGuardedByAssignees')->willReturn(true);
        $this->transitionPossibilityChecker->method('executorIsAssignee')->willReturn(false);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEmpty($result);
    }

    public function testGettingTasksWhenAssignedToOneTransitionOnly() {
        $this->workflow->method('getTransitions')->willReturn(
            [$this->createWorkflowTransitionMock(), $this->createWorkflowTransitionMock()]
        );
        $this->transitionPossibilityChecker->method('isTransitionGuardedByAssignees')->willReturnOnConsecutiveCalls(false, true);
        $this->transitionPossibilityChecker->method('executorIsAssignee')->willReturnOnConsecutiveCalls(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [$this->resource])], $result);
    }
}
