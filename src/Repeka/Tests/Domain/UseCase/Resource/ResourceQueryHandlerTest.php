<?php
namespace Repeka\Tests\Domain\UseCase\User;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceQuery;
use Repeka\Domain\UseCase\Resource\ResourceQueryHandler;

class ResourceQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceRepository */
    private $resourceRepository;

    /** @var ResourceQueryHandler */
    private $handler;

    protected function setUp() {
        $this->resourceRepository = $this->createMock(ResourceRepository::class);
        $this->handler = new ResourceQueryHandler($this->resourceRepository);
    }

    public function testHandling() {
        $this->resourceRepository->expects($this->once())->method('findOne')->with(2);
        $this->handler->handle(new ResourceQuery(2));
    }
}
