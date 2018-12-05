<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommand;
use Repeka\Domain\UseCase\Resource\ResourceGodUpdateCommandHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceGodUpdateCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceGodUpdateCommandHandler */
    private $handler;
    /** @var ResourceEntity */
    private $resource;

    protected function setUp() {
        $resourceRepository = $this->createRepositoryStub(ResourceRepository::class, []);
        $this->handler = new ResourceGodUpdateCommandHandler($resourceRepository);
        $this->resource = new ResourceEntity($this->createResourceKindMock(), ResourceContents::empty());
    }

    public function testChangingResourceKindIfGiven() {
        $rk = $this->createResourceKindMock();
        $command = ResourceGodUpdateCommand::builder()->setResource($this->resource)->changeResourceKind($rk)->build();
        $this->handler->handle($command);
        $this->assertEquals($rk, $this->resource->getKind());
    }

    public function testDoesNotChangeResourceKindIfNotRequested() {
        $rk = $this->resource->getKind();
        $command = ResourceGodUpdateCommand::builder()->setResource($this->resource)->build();
        $this->handler->handle($command);
        $this->assertEquals($rk, $this->resource->getKind());
    }

    public function testChangingResourceClassWithResourcekind() {
        $rk = $this->createResourceKindMock(1, 'unicorns');
        $command = ResourceGodUpdateCommand::builder()->setResource($this->resource)->changeResourceKind($rk)->build();
        $this->handler->handle($command);
        $this->assertEquals('unicorns', $this->resource->getResourceClass());
    }

    public function testChangingResourceContents() {
        $command = ResourceGodUpdateCommand::builder()->setResource($this->resource)->setNewContents([1 => 2])->build();
        $this->handler->handle($command);
        $this->assertEquals([2], $this->resource->getContents()->getValuesWithoutSubmetadata(1));
    }
}
