<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Exception\DomainException;
use Repeka\Domain\Validation\MetadataConstraints\PathInsideUploadDirConstraint;
use Repeka\Tests\Traits\StubsTrait;

class PathInsideUploadDirConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PathInsideUploadDirConstraint */
    private $constraint;

    protected function setUp() {
        $this->constraint = new PathInsideUploadDirConstraint();
    }

    public function testAcceptsNormalPaths() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__);
        $this->constraint->validateSingle($this->createMetadataMock(), __FILE__);
    }

    public function testRejectsGoingUp() {
        $this->expectException(DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/../etc/passwd');
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/..');
    }

    public function testAcceptsFilesWithTwoDots() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/crazy..gitignore');
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/..gitignore');
    }

    public function testAcceptsDirectoriesWithTwoDots() {
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/..crazy/gitignore');
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/crazy../gitignore');
        $this->constraint->validateSingle($this->createMetadataMock(), __DIR__ . '/crazy..git/ignore');
    }
}
