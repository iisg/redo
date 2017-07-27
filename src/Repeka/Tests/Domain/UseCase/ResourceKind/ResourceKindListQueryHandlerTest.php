<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindListQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

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
        $this->resourceKindRepository
            ->expects($this->once())->method('findAllByResourceClass')->with('books')->willReturn($resourceKindList);
        $this->resourceKindRepository->expects($this->once())->method('findAllSystemResourceKinds')->willReturn($resourceKindList);
        $returnedList = $this->handler->handle(new ResourceKindListQuery('books'));
        $this->assertCount(2, $returnedList);
    }

    public function testGettingTheListWithoutSystemResourceKinds() {
        $resourceKindList = [$this->createMock(ResourceKind::class)];
        $this->resourceKindRepository
            ->expects($this->once())->method('findAllByResourceClass')->with('books')->willReturn($resourceKindList);
        $this->resourceKindRepository->expects($this->never())->method('findAllSystemResourceKinds');
        $returnedList = $this->handler->handle(new ResourceKindListQuery('books', false));
        $this->assertSame($resourceKindList, $returnedList);
    }
}
