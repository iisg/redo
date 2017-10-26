<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindByResourceClassListQuery;
use Repeka\Domain\UseCase\ResourceKind\ResourceKindByResourceClassListQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceKindByResourceClassListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceKindRepository */
    private $resourceKindRepository;
    /** @var ResourceKindByResourceClassListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->resourceKindRepository = $this->createMock(ResourceKindRepository::class);
        $this->handler = new ResourceKindByResourceClassListQueryHandler($this->resourceKindRepository);
    }

    public function testGettingTheList() {
        $resourceKindList = [$this->createMock(ResourceKind::class)];
        $this->resourceKindRepository
            ->expects($this->once())->method('findAllByResourceClass')->with('books')->willReturn($resourceKindList);
        $returnedList = $this->handler->handle(new ResourceKindByResourceClassListQuery('books'));
        $this->assertCount(1, $returnedList);
    }
}
