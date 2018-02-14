<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
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
        $pageResult = new PageResult($resources, 1);
        $this->resourceRepository->expects($this->once())->method('findByQuery')->willReturn($pageResult);
        $returnedList = $this->handler->handle(ResourceListQuery::builder()->build());
        $this->assertSame($pageResult, $returnedList);
    }

    public function testGettingResults() {
        $resources = [$this->createMock(ResourceEntity::class)];
        $pageResult = new PageResult($resources, 1);
        $resourceChildrenQueryBuilder = ResourceListQuery::builder()->filterByParentId(123)->build();
        $this->resourceRepository->expects($this->once())->method('findByQuery')
            ->with($resourceChildrenQueryBuilder)->willReturn($pageResult);
        $result = $this->handler->handle($resourceChildrenQueryBuilder);
        $this->assertEquals($pageResult, $result);
    }
}
