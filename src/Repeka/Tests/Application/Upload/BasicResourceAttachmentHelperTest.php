<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Upload\BasicResourceAttachmentHelper;
use Repeka\Application\Upload\FilesystemDriver;
use Repeka\Application\Upload\ResourceAttachmentPathGenerator;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Upload\ResourceAttachmentHelper;
use Repeka\Tests\Traits\StubsTrait;

class BasicResourceAttachmentHelperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    // not constants because only variables work with string interpolation
    private $uploadsRoot = '/var/uploads/whatever';
    private $destinationPath = 'q/w/e/r/t/y/abcxyz';

    /** @var ResourceAttachmentPathGenerator|\PHPUnit_Framework_MockObject_MockObject */
    private $pathGenerator;
    /** @var FilesystemDriver|\PHPUnit_Framework_MockObject_MockObject */
    private $filesystemDriver;
    /** @var ResourceAttachmentHelper */
    private $helper;

    protected function setUp() {
        $this->pathGenerator = $this->createMock(ResourceAttachmentPathGenerator::class);
        $this->pathGenerator->method('getDestinationPath')->willReturn($this->destinationPath);
        $this->pathGenerator->method('getUploadsRootPath')->willReturn($this->uploadsRoot);
        $this->filesystemDriver = $this->createMock(FilesystemDriver::class);
        $this->helper = new BasicResourceAttachmentHelper($this->pathGenerator, $this->filesystemDriver);
    }

    public function testMovingFilesToDestinationPaths() {
        $fileBaseMetadataId = 1;
        $otherBaseMetadataId = $fileBaseMetadataId + 1;
        $resourceKind = $this->createResourceKindMock([
            $this->createMetadataMock(11, $fileBaseMetadataId, 'file'),
            $this->createMetadataMock(12, $otherBaseMetadataId, 'dummy'),
        ]);
        $contents = [
            $fileBaseMetadataId => ['somewhere/todo.list', 'somewhere/cuteCat.jpg'],
            $otherBaseMetadataId => ['somewhere/cantTouchThis', 'somewhere/nanananana.batman'],
        ];
        $resource = $this->createResourceMock(123, $resourceKind, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->atLeastOnce())
            ->method('mkdirRecursive')->with($this->uploadsRoot . '/' . $this->destinationPath, 0750);
        $this->filesystemDriver->expects($this->exactly(2))->method('move')->withConsecutive(
            ["$this->uploadsRoot/somewhere/todo.list", "$this->uploadsRoot/$this->destinationPath/todo.list"],
            ["$this->uploadsRoot/somewhere/cuteCat.jpg", "$this->uploadsRoot/$this->destinationPath/cuteCat.jpg"]
        );
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(
            function ($updatedContents) use ($contents, $fileBaseMetadataId, $otherBaseMetadataId) {
                $this->assertEquals(
                    ["$this->destinationPath/todo.list", "$this->destinationPath/cuteCat.jpg",],
                    $updatedContents[$fileBaseMetadataId]
                );
                $this->assertEquals($contents[$otherBaseMetadataId], $updatedContents[$otherBaseMetadataId]);
            }
        );
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testFilesAreNotMovedWhenTheyAreInCorrectPath() {
        $fileBaseMetadataId = 1;
        $resourceKind = $this->createResourceKindMock([
            $this->createMetadataMock(11, $fileBaseMetadataId, 'file'),
        ]);
        $contents = [
            $fileBaseMetadataId => ["$this->destinationPath/todo.list"],
        ];
        $resource = $this->createResourceMock(123, $resourceKind, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->never())->method('move');
        $this->filesystemDriver->expects($this->never())->method('exists');
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testMovingFilesFailsIfFileAlreadyExists() {
        $this->expectException(DomainException::class);
        $fileBaseMetadataId = 1;
        $resourceKind = $this->createResourceKindMock([
            $this->createMetadataMock(11, $fileBaseMetadataId, 'file'),
        ]);
        $contents = [
            $fileBaseMetadataId => [
                'somewhere/regular.file',
                'somewhere/file.that.already.exists',
            ],
        ];
        $resource = $this->createResourceMock(123, $resourceKind, $contents);
        $this->filesystemDriver->expects($this->exactly(2))->method('exists')->willReturnCallback(function ($path) {
            return (basename($path) != 'regular.file');
        });
        $this->filesystemDriver->expects($this->never())->method('move');
        $this->helper->moveFilesToDestinationPaths($resource);
    }
}
