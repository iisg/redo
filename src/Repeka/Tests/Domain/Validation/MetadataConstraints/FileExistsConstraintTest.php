<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\Validation\MetadataConstraints\FileExistsConstraint;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

class FileExistsConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var FileExistsConstraint */
    private $constraint;

    protected function setUp() {
        $storage = $this->createMock(ResourceFileStorage::class);
        $storage->method('getFileSystemPath')->willReturnArgument(1);
        $this->constraint = new FileExistsConstraint($storage);
    }

    public function testAcceptsFile() {
        $this->constraint->validateSingle($this->createMetadataMock(), __FILE__, $this->createResourceMock(1));
    }

    public function testRejectsDirectory() {
        $this->expectException(ValidationException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__, $this->createResourceMock(1));
    }

    public function testRejectsNotExistingFile() {
        $this->expectException(ValidationException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), 'unicorn', $this->createResourceMock(1));
    }
}
