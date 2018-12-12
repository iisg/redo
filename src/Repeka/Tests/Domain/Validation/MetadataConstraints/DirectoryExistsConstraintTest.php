<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Service\ResourceFileStorage;
use Repeka\Domain\Validation\MetadataConstraints\DirectoryExistsConstraint;
use Repeka\Tests\Traits\StubsTrait;
use Respect\Validation\Exceptions\ValidationException;

class DirectoryExistsConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var DirectoryExistsConstraint */
    private $constraint;

    protected function setUp() {
        $storage = $this->createMock(ResourceFileStorage::class);
        $storage->method('getFileSystemPath')->willReturnArgument(1);
        $this->constraint = new DirectoryExistsConstraint($storage);
    }

    public function testAcceptsDirectory() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__, $this->createResourceMock(1));
    }

    public function testRejectsFile() {
        $this->expectException(ValidationException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), __FILE__, $this->createResourceMock(1));
    }

    public function testRejectsNotExistingFile() {
        $this->expectException(ValidationException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), 'unicorn', $this->createResourceMock(1));
    }
}
