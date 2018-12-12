<?php
namespace Repeka\Domain\Metadata\MetadataValueAdjuster;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\MetadataValue;

class FileAndDirectoryMetadataValueAdjusterTest extends \PHPUnit_Framework_TestCase {
    /** @var FileAndDirectoryMetadataValueAdjuster */
    private $adjuster;

    protected function setUp() {
        $this->adjuster = new FileAndDirectoryMetadataValueAdjuster();
    }

    public function testDecodesPaths() {
        $this->assertEquals(
            new MetadataValue('dir/file name.txt'),
            $this->adjuster->adjustMetadataValue(new MetadataValue('dir/file%20name.txt'), MetadataControl::FILE())
        );
    }

    public function testConvertsWindowsSlashedToUnix() {
        $this->assertEquals(
            new MetadataValue('dir/filename.txt'),
            $this->adjuster->adjustMetadataValue(new MetadataValue('dir\filename.txt'), MetadataControl::FILE())
        );
    }
}
