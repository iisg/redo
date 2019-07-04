<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\MetadataConstraints\AllowedFileExtensionsConstraint;
use Repeka\Tests\Traits\StubsTrait;

class AllowedFileExtensionsConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var AllowedFileExtensionsConstraint */
    private $constraint;
    /** @var Metadata|\Framework_MockObjectTest */
    private $metadata;
    /** @var ResourceEntity|\Framework_MockObjectTest */
    private $resource;

    public function setUp() {
        $this->constraint = new AllowedFileExtensionsConstraint();
        $this->metadata = $this->createMetadataMock(1, null, MetadataControl::FILE(), ['allowedFileExtensions' => ['txt', 'pdf']]);
        $this->resource = $this->createResourceMock(1);
    }

    public function testRejectsFileWithInvalidExtension() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->metadata, "picture.png", $this->resource);
    }

    public function testRejectsFileWithoutExtension() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->metadata, "somefile", $this->resource);
    }

    public function testAcceptsFilesWithCorrectExtensions() {
        $this->constraint->validateSingle($this->metadata, "test.txt", $this->resource);
        $this->constraint->validateSingle($this->metadata, "test.pdf", $this->resource);
    }

    public function testCheckingFileExtensionsIsCaseInsensitive() {
        $this->constraint->validateSingle($this->metadata, "test.PDF", $this->resource);
        $this->constraint->validateSingle($this->metadata, "test.TxT", $this->resource);
    }
}
