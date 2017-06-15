<?php
namespace Repeka\Tests\Application\Upload;

use Assert\InvalidArgumentException;
use Repeka\Application\Upload\ResourceAttachmentPathGenerator;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceAttachmentPathGeneratorTest extends \PHPUnit_Framework_TestCase {
    /** @var ResourceAttachmentPathGenerator */
    private $pathGenerator;

    protected function setUp() {
        $this->pathGenerator = new ResourceAttachmentPathGenerator('testUploadsRoot', 'testTempFolder');
    }

    public function testFailsWhenResourceIdIsMissing() {
        $this->expectException(InvalidArgumentException::class);
        $resource = $this->createMock(ResourceEntity::class);
        $this->pathGenerator->getDestinationPath($resource);
    }

    public function testReturnsRelativePath() {
        $resource = $this->createMock(ResourceEntity::class);
        $resource->expects($this->atLeastOnce())->method('getId')->willReturn(1234);
        $result = $this->pathGenerator->getDestinationPath($resource);
        $this->assertEquals('i1/i2/i3/i4/r1234', $result);
    }

    public function testReturnsUploadsRoot() {
        $this->assertEquals('testUploadsRoot', $this->pathGenerator->getUploadsRootPath());
    }

    public function testReturnsTemporaryFolderName() {
        $this->assertEquals('testTempFolder', $this->pathGenerator->getTemporaryFolderName());
    }

    public function testReturnsTemporaryPath() {
        $this->assertEquals('testUploadsRoot/testTempFolder', $this->pathGenerator->getTemporaryPath());
    }
}
