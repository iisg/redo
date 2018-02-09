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

    protected function setUp() {
        $fileHelper = $this->createMock(ResourceFileHelper::class);
        $fileHelper->method('toAbsolutePath')->willReturnCallback(function (string $path) {
            return 'absolute/' . $path;
        });
        $this->handler = new ResourceFileQueryHandler($fileHelper);
        $this->resourceKind = $this->createResourceKindMock(1, 'books', [
            $this->createMetadataMock(11),
            $this->createMetadataMock(12),
            $this->createMetadataMock(13),
        ]);
        $this->resourceKind->method('getMetadataByControl')
            ->willReturn([$this->createMetadataMock(11), $this->createMetadataMock(12)]);
        $this->resource = $this->createResourceMock(1, $this->resourceKind, [11 => ['relative/path/test.txt']]);
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
        $resource = new ResourceEntity($this->resourceKind, [13 => ['relative/path/test.txt']]);
        $this->handler->handle(new ResourceFileQuery($resource, 'test.txt'));
    }
}
