<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryHandler;

class ResourceListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceRepository */
    private $resourceRepository;
    /** @var ResourceListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->handler = new ResourceListQueryHandler($this->resourceRepository);
    }

    public function testGettingTheList() {
        $resources = [$this->createMock(ResourceEntity::class)];
        $this->resourceRepository->expects($this->once())->method('findAllNonSystemResources')->willReturn($resources);
        $returnedList = $this->handler->handle(new ResourceListQuery());
        $this->assertSame($resources, $returnedList);
    }

    public function testGettingTheListWithSystemResources() {
        $resources = [$this->createMock(ResourceEntity::class)];
        $this->resourceRepository->expects($this->once())->method('findAll')->willReturn($resources);
        $returnedList = $this->handler->handle(new ResourceListQuery(true));
        $this->assertSame($resources, $returnedList);
    }
}
