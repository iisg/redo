<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Validation\MetadataConstraints\PathInsideUploadDirConstraint;
use Repeka\Tests\Traits\StubsTrait;

class PathInsideUploadDirConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PathInsideUploadDirConstraint */
    private $constraint;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    protected function setUp() {
        $this->constraint = new PathInsideUploadDirConstraint();
        $this->resource = $this->createResourceMock(1);
    }

    public function testAcceptsNormalPaths() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__, $this->resource);
        $this->constraint->validateSingle($this->createMetadataMock(), __FILE__, $this->resource);
    }

    public function testRejectsGoingUp() {
        $this->expectException(DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/../etc/passwd', $this->resource);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/..', $this->resource);
    }

    public function testAcceptsFilesWithTwoDots() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/crazy..gitignore', $this->resource);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/..gitignore', $this->resource);
    }

    public function testAcceptsDirectoriesWithTwoDots() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/..crazy/gitignore', $this->resource);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/crazy../gitignore', $this->resource);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/crazy..git/ignore', $this->resource);
    }
}
