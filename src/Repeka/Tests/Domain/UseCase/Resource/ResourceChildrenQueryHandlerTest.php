<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceChildrenQuery;
use Repeka\Domain\UseCase\Resource\ResourceChildrenQueryHandler;

class ResourceChildrenQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;
    /** @var ResourceChildrenQueryHandler */
    private $handler;

    protected function setUp() {
        parent::setUp();
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->handler = new ResourceChildrenQueryHandler($this->resourceRepository);
    }

    public function testGettingResults() {
        $this->resourceRepository->expects($this->once())->method('findChildren')->with(123)->willReturn(['test']);
        $result = $this->handler->handle(new ResourceChildrenQuery(123));
        $this->assertEquals(['test'], $result);
    }
}
