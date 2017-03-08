<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommandHandler;

class ResourceTransitionCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    private $resourceRepository;

    /** @var ResourceTransitionCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->resourceRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->handler = new ResourceTransitionCommandHandler($this->resourceRepository);
    }

    public function testTransition() {
        $command = new ResourceTransitionCommand($this->resource, 't1');
        $this->resource->expects($this->once())->method('applyTransition')->with('t1');
        $this->handler->handle($command);
    }
}
