<?php
namespace Repeka\Tests\Application\Upload;

use Repeka\Application\Upload\BasicResourceFileHelper;
use Repeka\Application\Upload\FilesystemDriver;
use Repeka\Application\Upload\ResourceFilePathGenerator;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Tests\Traits\StubsTrait;

class BasicResourceFileHelperTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    private const FILE_METADATA_ID = 1;
    private const OTHER_METADATA_ID = 2;

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
        $fileMetadataMock = $this->createMetadataMock(self::FILE_METADATA_ID, 1, MetadataControl::FILE());
        $metadataRepository = $this->createRepositoryStub(MetadataRepository::class, [
            $fileMetadataMock,
            $this->createMetadataMock(self::OTHER_METADATA_ID, 1, MetadataControl::TEXT()),
        ]);
        $metadataRepository->method('findByControlAndResourceClass')->willReturn([$fileMetadataMock]);
        $this->helper = new BasicResourceFileHelper($this->pathGenerator, $this->filesystemDriver, $metadataRepository);
    }

    public function testMovingFilesToDestinationPaths() {
        $contents = ResourceContents::fromArray([
            self::FILE_METADATA_ID => ['somewhere/todo.list', 'somewhere/cuteCat.jpg'],
            self::OTHER_METADATA_ID => ['somewhere/cantTouchThis', 'somewhere/nanananana.batman'],
        ])->toArray();
        $resource = $this->createResourceMock(123, null, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->atLeastOnce())
            ->method('mkdirRecursive')->with($this->uploadsRoot . '/' . $this->destinationPath, 0750);
        $this->filesystemDriver->expects($this->exactly(2))->method('move')->withConsecutive(
            ["$this->uploadsRoot/somewhere/todo.list", "$this->uploadsRoot/$this->destinationPath/todo.list"],
            ["$this->uploadsRoot/somewhere/cuteCat.jpg", "$this->uploadsRoot/$this->destinationPath/cuteCat.jpg"]
        );
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(
            function ($updatedContents) use ($contents) {
                $this->assertEquals(
                    ResourceContents::fromArray([["$this->destinationPath/todo.list", "$this->destinationPath/cuteCat.jpg"]])[0],
                    $updatedContents[self::FILE_METADATA_ID]
                );
                $this->assertEquals($contents[self::OTHER_METADATA_ID], $updatedContents[self::OTHER_METADATA_ID]);
            }
        );
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testMovingFilesToDestinationPathsEvenIfFilesInSubmetadata() {
        $contents = ResourceContents::fromArray([
            self::OTHER_METADATA_ID => [['submetadata' => [self::FILE_METADATA_ID => ['somewhere/todo.list', 'somewhere/cuteCat.jpg']]]],
        ])->toArray();
        $resource = $this->createResourceMock(123, null, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->atLeastOnce())
            ->method('mkdirRecursive')->with($this->uploadsRoot . '/' . $this->destinationPath, 0750);
        $this->filesystemDriver->expects($this->exactly(2))->method('move')->withConsecutive(
            ["$this->uploadsRoot/somewhere/todo.list", "$this->uploadsRoot/$this->destinationPath/todo.list"],
            ["$this->uploadsRoot/somewhere/cuteCat.jpg", "$this->uploadsRoot/$this->destinationPath/cuteCat.jpg"]
        );
        $resource->expects($this->once())->method('updateContents')->willReturnCallback(
            function (ResourceContents $updatedContents) use ($contents) {
                $this->assertEquals(
                    ["$this->destinationPath/todo.list", "$this->destinationPath/cuteCat.jpg"],
                    $updatedContents->getValues(self::FILE_METADATA_ID)
                );
            }
        );
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testFilesAreNotMovedWhenTheyAreInCorrectPath() {
        $contents = ResourceContents::fromArray([
            self::FILE_METADATA_ID => ["$this->destinationPath/todo.list"],
        ])->toArray();
        $resource = $this->createResourceMock(123, null, $contents);
        $this->pathGenerator->expects($this->atLeastOnce())->method('getDestinationPath')->with($resource);
        $this->filesystemDriver->expects($this->never())->method('move');
        $this->filesystemDriver->expects($this->never())->method('exists');
        $this->helper->moveFilesToDestinationPaths($resource);
    }

    public function testMovingFilesFailsIfFileAlreadyExists() {
        $this->expectException(DomainException::class);
        $contents = ResourceContents::fromArray([
            self::FILE_METADATA_ID => [
                'somewhere/regular.file',
                'somewhere/file.that.already.exists',
            ],
        ])->toArray();
        $resource = $this->createResourceMock(123, null, $contents);
        $this->filesystemDriver->expects($this->exactly(2))->method('exists')->willReturnCallback(function ($path) {
            return (basename($path) != 'regular.file');
        });
        $this->filesystemDriver->expects($this->never())->method('move');
        $this->helper->moveFilesToDestinationPaths($resource);
    }
}
