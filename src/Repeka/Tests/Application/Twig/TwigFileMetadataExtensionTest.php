<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Twig\TwigFileMetadataExtension;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Service\FileSystemDriver;
use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Tests\Traits\StubsTrait;

class TwigFileMetadataExtensionTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var MetadataRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var ResourceFileStorage|\PHPUnit_Framework_MockObject_MockObject */
    private $resourceFileStorage;
    /** @var FileSystemDriver|\PHPUnit_Framework_MockObject_MockObject */
    private $fileSystemDriver;
    /** @var TwigFileMetadataExtension */
    private $extension;

    /** @before */
    public function init() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->resourceFileStorage = $this->createMock(ResourceFileStorage::class);
        $this->fileSystemDriver = $this->createMock(FileSystemDriver::class);
        $this->extension = new TwigFileMetadataExtension(
            $this->metadataRepository,
            $this->resourceFileStorage,
            $this->fileSystemDriver
        );
    }

    public function testListFilesFromDirectoryMetadata() {
        $resource = $this->createResourceMock(1, null, [1 => 'testFolder']);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::DIRECTORY());
        $this->resourceFileStorage->method('getDirectoryContents')->willReturn(['testFolder/fileA', 'testFolder/fileB']);
        $actualFiles = $this->extension->metadataFiles($resource, $metadata);
        $this->assertEquals(['testFolder/fileA', 'testFolder/fileB'], $actualFiles);
    }

    public function testListFilesFromFileMetadata() {
        $resource = $this->createResourceMock(1, null, [1 => ['fileA', 'fileB']]);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->metadataRepository->method('findByNameOrId')->willReturn($metadata);
        $actualFiles = $this->extension->metadataFiles($resource, $metadata);
        $this->assertEquals(['fileA', 'fileB'], $actualFiles);
    }

    public function testListFilesFilteringByExtension() {
        $resource = $this->createResourceMock(1, null, [1 => ['fileGood.txt', 'fileBad.png']]);
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->metadataRepository->method('findByNameOrId')->willReturn($metadata);
        $actualFiles = $this->extension->metadataFiles($resource, $metadata, ['txt']);
        $this->assertEquals(['fileGood.txt'], $actualFiles);
    }

    public function testGettingFileSize() {
        $resource = $this->createResourceMock(1);
        $this->fileSystemDriver->method('getFileSize')->willReturn(123);
        $size = $this->extension->metadataFileSize([], 'a.txt', $resource);
        $this->assertEquals(123, $size);
    }

    public function testGettingFileSizeForArrayOfFiles() {
        $resource = $this->createResourceMock(1);
        $this->fileSystemDriver->method('getFileSize')->willReturnOnConsecutiveCalls(123, 234);
        $size = $this->extension->metadataFileSize([], ['a.txt', 'b.txt'], $resource);
        $this->assertEquals([123, 234], $size->toArray());
    }

    public function testUsingResourceFromContext() {
        $resource = $this->createResourceMock(1);
        $this->fileSystemDriver->method('getFileSize')->willReturnOnConsecutiveCalls(123, 234);
        $size = $this->extension->metadataFileSize(['resource' => $resource], ['a.txt', 'b.txt']);
        $this->assertEquals([123, 234], $size->toArray());
    }
}
