<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQueryHandler;

class ResourceKindListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceKindListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->handler = new ResourceKindListQueryHandler($this->resourceKindRepository);
    }

    public function testGettingTheList() {
        $resourceKindList = [$this->createMock(ResourceKind::class)];
        $this->resourceKindRepository->expects($this->once())->method('findAll')->willReturn($resourceKindList);
        $returnedList = $this->handler->handle(new ResourceKindListQuery());
        $this->assertSame($resourceKindList, $returnedList);
    }
}
