<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTransitionCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;
    /** @var User */
    private $executor;
    /** @var ResourceWorkflow|\PHPUnit_Framework_MockObject_MockObject */
    private $workflow;

    /** @var ResourceTransitionCommandHandler */
    private $handler;

    protected function setUp() {
        $this->workflow = $this->createMock(ResourceWorkflow::class);
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resource->method('getWorkflow')->willReturn($this->workflow);
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class);
        $this->executor = $this->createMock(User::class);
        $this->handler = new ResourceTransitionCommandHandler($resourceRepository);
    }

    public function testTransition() {
        $this->workflow->method('isTransitionPossible')->willReturn(true);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->executor);
        $this->resource->expects($this->once())->method('applyTransition')->with('t1');
        $this->handler->handle($command);
    }

    public function testDisallowedTransition() {
        $this->expectException(DomainException::class);
        $this->workflow->method('isTransitionPossible')->willReturn(false);
        $command = new ResourceTransitionCommand($this->resource, 't1', $this->executor);
        $this->handler->handle($command);
    }
}
