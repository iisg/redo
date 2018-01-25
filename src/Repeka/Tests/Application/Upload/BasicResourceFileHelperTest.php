<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Upload\BasicResourceFileHelper;
use Repeka\Application\Upload\FilesystemDriver;
use Repeka\Application\Upload\ResourceFilePathGenerator;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Tests\Traits\ResourceContentsNormalizerAware;
use Repeka\Tests\Traits\StubsTrait;

class BasicResourceFileHelperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;
    use ResourceContentsNormalizerAware;

    // not constants because only variables work with string interpolation
    private $uploadsRoot = '/var/uploads/whatever';
    private $destinationPath = 'q/w/e/r/t/y/abcxyz';

    /** @var ResourceFilePathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    private $pathGenerator;
    /** @var FilesystemDriver|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystemDriver;
    /** @var ResourceFileHelper */
    private $helper;

    protected function setUp() {
        $this->pathGenerator = $this->createMock(ResourceFilePathGenerator::class);
        $this->pathGenerator->method('getDestinationPath')->willReturn($this->destinationPath);
        $this->pathGenerator->method('getUploadsRootPath')->willReturn($this->uploadsRoot);
        $this->filesystemDriver = $this->createMock(FilesystemDriver::class);
        $this->helper = new BasicResourceFileHelper($this->pathGenerator, $this->filesystemDriver);
    }

    public function testMovingFilesToDestinationPaths() {
        $fileMetadataId = 1;
        $otherMetadataId = $fileMetadataId + 1;
        $resourceKind = $this->createResourceKindMock(1, 'books', [
            $this->createMetadataMock($fileMetadataId, 66, MetadataControl::FILE()),
            $this->createMetadataMock($otherMetadataId, 66, MetadataControl::TEXTAREA()),
        ]);
        $contents = $this->normalizeContents([
            $fileMetadataId => ['somewhere/todo.list', 'somewhere/cuteCat.jpg'],
            $otherMetadataId => ['somewhere/cantTouchThis', 'somewhere/nanananana.batman'],
        ]);
        $resource = $this->createResourceMock(123, $resourceKind, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->atLeastOnce())
            ->method('mkdirRecursive')->with($this->uploadsRoot . '/' . $this->destinationPath, 0750);
        $this->filesystemDriver->expects($this->exactly(2))->method('move')->withConsecutive(
            ["$this->uploadsRoot/somewhere/todo.list", "$this->uploadsRoot/$this->destinationPath/todo.list"],
            ["$this->uploadsRoot/somewhere/cuteCat.jpg", "$this->uploadsRoot/$this->destinationPath/cuteCat.jpg"]
        );
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(
            function ($updatedContents) use ($contents, $fileMetadataId, $otherMetadataId) {
                $this->assertEquals(
                    $this->normalizeContents([["$this->destinationPath/todo.list", "$this->destinationPath/cuteCat.jpg"]])[0],
                    $updatedContents[$fileMetadataId]
                );
                $this->assertEquals($contents[$otherMetadataId], $updatedContents[$otherMetadataId]);
            }
        );
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testFilesAreNotMovedWhenTheyAreInCorrectPath() {
        $fileMetadataId = 1;
        $resourceKind = $this->createResourceKindMock(1, 'books', [
            $this->createMetadataMock($fileMetadataId, 66, MetadataControl::FILE()),
        ]);
        $contents = $this->normalizeContents([
            $fileMetadataId => ["$this->destinationPath/todo.list"],
        ]);
        $resource = $this->createResourceMock(123, $resourceKind, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->never())->method('move');
        $this->filesystemDriver->expects($this->never())->method('exists');
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testMovingFilesFailsIfFileAlreadyExists() {
        $this->expectException(DomainException::class);
        $fileMetadataId = 1;
        $resourceKind = $this->createResourceKindMock(1, 'books', [
            $this->createMetadataMock($fileMetadataId, 66, MetadataControl::FILE()),
        ]);
        $contents = $this->normalizeContents([
            $fileMetadataId => [
                'somewhere/regular.file',
                'somewhere/file.that.already.exists',
            ],
        ]);
        $resource = $this->createResourceMock(123, $resourceKind, $contents);
        $this->filesystemDriver->expects($this->exactly(2))->method('exists')->willReturnCallback(function ($path) {
            return (basename($path) != 'regular.file');
        });
        $this->filesystemDriver->expects($this->never())->method('move');
        $this->helper->moveFilesToDestinationPaths($resource);
    }
}
