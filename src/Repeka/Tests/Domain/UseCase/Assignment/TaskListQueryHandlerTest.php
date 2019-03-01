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
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Domain\Workflow\TransitionAssigneeChecker;
use Repeka\Domain\Workflow\TransitionPossibilityChecker;
use Repeka\Tests\Traits\StubsTrait;

class TaskListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceRepository */
    private $resourceRepository;
    /** @var TaskListQueryHandler */
    private $handler;
    /** @var TransitionPossibilityChecker|PHPUnit_Framework_MockObject_MockObject */
    private $transitionAssigneeChecker;
    /** @var ResourceWorkflow|PHPUnit_Framework_MockObject_MockObject */
    private $workflow;
    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var User|PHPUnit_Framework_MockObject_MockObject */
    private $user;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->transitionAssigneeChecker = $this->createMock(TransitionAssigneeChecker::class);
        $this->handler = new TaskListQueryHandler($this->resourceRepository, $this->transitionAssigneeChecker);
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createResourceMock(1, $this->createResourceKindMock(1, 'books', [], $this->workflow));
        $this->user = $this->createMock(User::class);
        $this->user->method('getUserData')->willReturn($this->createResourceMock(100));
    }

    public function testGettingTasksWhenAssignee() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturn([100]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [$this->resource])], $result);
    }

    public function testGettingTasksWhenNoOneAssigned() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturn([]);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEmpty($result);
    }

    public function testGettingTasksWhenOtherUserIsAssignee() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturn([101]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(false);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEmpty($result);
    }

    public function testGettingTasksWhenAssignedToOneTransitionOnly() {
        $this->workflow->method('getTransitions')->willReturn(
            [$this->createWorkflowTransitionMock(), $this->createWorkflowTransitionMock()]
        );
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturnOnConsecutiveCalls([], [100]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturnOnConsecutiveCalls(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [$this->resource])], $result);
    }

    public function testTaskIsPossibleWhenSomebodyElseIsAssignedToo() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturn([100, 101]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [], [$this->resource])], $result);
    }

    public function testTaskIsPossibleWhenUserIdIsNotExpliciteUsed() {
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturn([101]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [], [$this->resource])], $result);
    }

    public function testTaskIsMineWhenAtLeastOneTransitionSaysSo() {
        $this->workflow->method('getTransitions')->willReturn(
            [$this->createWorkflowTransitionMock(), $this->createWorkflowTransitionMock()]
        );
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')->willReturnOnConsecutiveCalls([101], [100]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturnOnConsecutiveCalls(true, true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn([$this->resource]);
        $result = $this->handler->handle(new TaskListQuery($this->user));
        $this->assertEquals([new TasksCollection('books', [$this->resource])], $result);
    }

    public function testTasksAreSortedById() {
        $resourceKind = $this->createResourceKindMock(1, 'books', [], $this->workflow);
        $resources = [];
        for ($i = 1; $i < 10; $i++) {
            $resources[] = $this->createResourceMock($i, $resourceKind);
        }
        $this->workflow->method('getTransitions')->willReturn([$this->createWorkflowTransitionMock()]);
        $this->transitionAssigneeChecker->method('getUserIdsAssignedToTransition')
            ->willReturnOnConsecutiveCalls([101], [100], [101], [100], [101], [100], [101], [100], [101]);
        $this->transitionAssigneeChecker->method('canApplyTransition')->willReturn(true);
        $this->resourceRepository->expects($this->once())->method('findAssignedTo')->with($this->user)->willReturn($resources);
        $expectedMyTasksIds = [2, 4, 6, 8];
        $expectedPossibleTasksIds = [1, 3, 5, 7, 9];
        /** @var TasksCollection $result */
        $result = $this->handler->handle(new TaskListQuery($this->user))[0] ?? null;
        $this->assertNotNull($result);
        $this->assertEquals($expectedMyTasksIds, EntityUtils::mapToIds($result->getMyTasks()));
        $this->assertEquals($expectedPossibleTasksIds, EntityUtils::mapToIds($result->getPossibleTasks()));
    }
}
