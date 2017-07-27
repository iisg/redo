<?php

namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Resource\ResourceFileQuery;
use Repeka\Domain\UseCase\Resource\ResourceFileQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceFileQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileQueryHandler */
    private $handler;
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceKind */
    private $resourceKind;
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceEntity */
    private $resource;
    private $resourceClass;

    protected function setUp() {
        $fileHelper = $this->createMock(ResourceFileHelper::class);
        $fileHelper->method('toAbsolutePath')->willReturnCallback(function (string $path) {
            return 'absolute/' . $path;
        });
        $this->handler = new ResourceFileQueryHandler($fileHelper);
        $this->resourceKind = $this->createResourceKindMock([
            $this->createMetadataMock(11, 1),
            $this->createMetadataMock(12, 2),
            $this->createMetadataMock(13, 3),
        ]);
        $this->resourceKind->method('getMetadataByControl')
            ->willReturn([$this->createMetadataMock(11, 1), $this->createMetadataMock(12, 2)]);
        $this->resourceClass = 'books';
        $this->resource = $this->createResourceMock(1, $this->resourceKind, [1 => ['relative/path/test.txt']]);
    }

    public function testGettingFile() {
        $filePath = $this->handler->handle(new ResourceFileQuery($this->resource, 'test.txt'));
        $this->assertEquals('absolute/relative/path/test.txt', $filePath);
    }

    public function testGettingNonExistingFile() {
        $this->expectException(NotFoundException::class);
        $this->handler->handle(new ResourceFileQuery($this->resource, 'test2.txt'));
    }

    public function testGettingFileFromNotFileMetadata() {
        $this->expectException(NotFoundException::class);
        $resource = new ResourceEntity($this->resourceKind, [3 => ['relative/path/test.txt']], $this->resourceClass);
        $this->handler->handle(new ResourceFileQuery($resource, 'test.txt'));
    }
}
