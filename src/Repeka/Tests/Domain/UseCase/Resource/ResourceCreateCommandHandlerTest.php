<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommand;
use Repeka\Domain\UseCase\Resource\ResourceCreateCommandHandler;

class ResourceCreateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    private $resourceKind;

    /** @var ResourceCreateCommand */
    private $command;

    private $resourceRepository;

    /** @var ResourceCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->resourceKind = new ResourceKind([]);
        $this->command = new ResourceCreateCommand($this->resourceKind, ['2' => 'AA']);
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->resourceRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $this->handler = new ResourceCreateCommandHandler($this->resourceRepository);
    }

    public function testCreatingResource() {
        $resource = $this->handler->handle($this->command);
        $this->assertNotNull($resource);
        $this->assertSame($this->command->getKind(), $resource->getKind());
        $this->assertSame($this->command->getContents(), $resource->getContents());
    }
}
