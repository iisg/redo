<?php
namespace Repeka\Tests\Domain\UseCase\ResourceKind;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\NotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\UseCase\Resource\ResourceFileQuery;
use Repeka\Domain\UseCase\Resource\ResourceFileQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class ResourceFileQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceFileQueryHandler */
    private $handler;
    /** @var \PHPUnit_Framework_MockObject_MockObject|ResourceEntity */
    private $resource;

    protected function setUp() {
        $fileHelper = $this->createMock(ResourceFileHelper::class);
        $fileHelper->method('toAbsolutePath')->willReturnCallback(function (string $path) {
            return 'absolute/' . $path;
        });
        $fileMetadataMock = $this->createMetadataMock(11, 1, MetadataControl::FILE());
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class, [
            $fileMetadataMock,
            $this->createMetadataMock(13, 1, MetadataControl::TEXT()),
        ]);
        $metadataRepository->method('findByControlAndResourceClass')->willReturn([$fileMetadataMock]);
        $this->handler = new ResourceFileQueryHandler($fileHelper, $metadataRepository);
        $this->resource = $this->createResourceMock(1, null, [11 => ['relative/path/test.txt']]);
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
        $resource = $this->createResourceMock(1, null, ResourceContents::fromArray([13 => ['relative/path/test.txt']]));
        $this->handler->handle(new ResourceFileQuery($resource, 'test.txt'));
    }
}
