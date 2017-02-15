<?php
namespace Repeka\Tests\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommandHandler;

class ResourceUpdateContentsCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceEntity|PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    /** @var ResourceUpdateContentsCommand */
    private $command;

    private $resourceRepository;

    /** @var ResourceUpdateContentsCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resource = $this->createMock(ResourceEntity::class);
        $this->command = new ResourceUpdateContentsCommand($this->resource, ['1' => 'AA']);
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->resourceRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->handler = new ResourceUpdateContentsCommandHandler($this->resourceRepository);
    }

    public function testCreatingResource() {
        $this->resource->expects($this->once())->method('updateContents')->with(['1' => 'AA']);
        $resource = $this->handler->handle($this->command);
        $this->assertSame($this->resource, $resource);
    }
}
