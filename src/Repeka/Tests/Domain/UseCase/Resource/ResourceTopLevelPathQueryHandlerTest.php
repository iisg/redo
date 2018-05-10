<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQuery;
use Repeka\Domain\UseCase\Resource\ResourceTopLevelPathQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTopLevelPathQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|ResourceRepository */
    private $resourceRepository;

    /** @var ResourceTopLevelPathQueryHandler */
    private $handler;

    protected function setUp() {
        $this->resourceRepository = $this->createRepositoryStub(
            ResourceRepository::class,
            [
                $this->createResourceMock(2, null, []),
                $this->createResourceMock(3, null, [10 => 2]),
            ]
        );
        $this->handler = new ResourceTopLevelPathQueryHandler($this->resourceRepository);
    }

    public function testEmptyPath() {
        $resource = $this->createResourceMock(1);
        $query = new ResourceTopLevelPathQuery($resource, 10);
        $path = $this->handler->handle($query);
        $this->assertEmpty($path);
    }

    public function testPathWithOneResource() {
        $resource = $this->createResourceMock(1, null, [10 => 2]);
        $query = new ResourceTopLevelPathQuery($resource, 10);
        $path = $this->handler->handle($query);
        $this->assertEquals([$this->resourceRepository->findOne(2)], $path);
    }

    public function testPathWithTwoResources() {
        $resource = $this->createResourceMock(1, null, [10 => 3]);
        $query = new ResourceTopLevelPathQuery($resource, 10);
        $path = $this->handler->handle($query);
        $this->assertEquals([$this->resourceRepository->findOne(3), $this->resourceRepository->findOne(2)], $path);
    }

    public function testPickFirstParentIfHasMoreThanOne() {
        $resource = $this->createResourceMock(1, null, [10 => [2, 3]]);
        $query = new ResourceTopLevelPathQuery($resource, 10);
        $path = $this->handler->handle($query);
        $this->assertEquals([$this->resourceRepository->findOne(2)], $path);
    }
}
