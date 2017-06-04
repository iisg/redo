<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandHandler;
use Repeka\Domain\Workflow\ResourceWorkflowTransitionHelper;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTransitionCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var User */
    private $executor;
    /** @var ResourceWorkflowTransitionHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $transitionHelper;

    /** @var ResourceTransitionCommandHandler */
    private $handler;

    protected function setUp() {
        $this->transitionHelper = $this->createMock(ResourceWorkflowTransitionHelper::class);
        $workflow = $this->createMock(ResourceWorkflow::class);
        $workflow->method('getTransitionHelper')->willReturn($this->transitionHelper);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->method('getWorkflow')->willReturn($workflow);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->executor = $this->createMock(User::class);
        $this->handler = new ResourceTransitionCommandHandler($resourceRepository);
    }

    public function testTransition() {
        $this->transitionHelper->method('transitionIsPossible')->willReturn(true);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->executor);
        $this->resource->expects($this->once())->method('applyTransition')->with('t1');
        $this->handler->handle($command);
    }

    public function testDisallowedTransition() {
        $this->transitionHelper->method('transitionIsPossible')->willReturn(false);
        $this->expectException(DomainException::class);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->executor);
        $this->handler->handle($command);
    }
}
