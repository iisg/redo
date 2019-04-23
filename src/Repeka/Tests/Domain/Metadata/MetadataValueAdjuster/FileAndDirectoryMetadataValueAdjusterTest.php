<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;
use Repeka\Tests\Traits\StubsTrait;

class FileAndDirectoryMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FileAndDirectoryMetadataValueAdjuster */
    private $adjuster;

    protected function setUp() {
        $this->adjuster = new FileAndDirectoryMetadataValueAdjuster();
    }

    public function testDecodesPaths() {
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->assertEquals(
            new MetadataValue('dir/file name.txt'),
            $this->adjuster->adjustMetadataValue(new MetadataValue('dir/file%20name.txt'), $metadata)
        );
    }

    public function testConvertsWindowsSlashedToUnix() {
        $metadata = $this->createMetadataMock(1, null, MetadataControl::FILE());
        $this->assertEquals(
            new MetadataValue('dir/filename.txt'),
            $this->adjuster->adjustMetadataValue(new MetadataValue('dir\filename.txt'), $metadata)
        );
    }
}
