<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommand;
use Repeka\Domain\UseCase\Resource\ResourceDeleteCommandHandler;

class ResourceDeleteCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceDeleteCommandHandler */
    private $handler;
    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->handler = new ResourceDeleteCommandHandler($this->resourceRepository);
    }

    public function testDeleting() {
        $resource = $this->createMock(ResourceEntity::class);
        $command = new ResourceDeleteCommand($resource);
        $this->resourceRepository->expects($this->once())->method('delete')->with($resource);
        $this->handler->handle($command);
    }
}
