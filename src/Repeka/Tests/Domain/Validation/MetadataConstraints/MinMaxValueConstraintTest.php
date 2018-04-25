<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Validation\MetadataConstraints\MinMaxValueConstraint;
use Repeka\Tests\Traits\StubsTrait;

class MinMaxValueConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  MinMaxValueConstraint */
    private $constraint;

    protected function setUp() {
        $this->constraint = new MinMaxValueConstraint();
    }

    public function testAcceptsValidConfig() {
        $this->assertTrue($this->constraint->isConfigValid(['min'=>0, 'max'=>100]));
        $this->assertTrue($this->constraint->isConfigValid(['max'=>2000]));
        $this->assertTrue($this->constraint->isConfigValid(['min'=>1900]));
        $this->assertTrue($this->constraint->isConfigValid([]));
    }

    public function testRejectsInvalidConfig() {
        $this->assertFalse($this->constraint->isConfigValid(['min'=>2000, 'max'=>10]));
        $this->assertFalse($this->constraint->isConfigValid(['min'=>0, 'max'=>100, 'foo'=>1000]));
        $this->assertFalse($this->constraint->isConfigValid(['min'=>'abc', 'max='>100]));
    }

    public function testRejectsMinMaxRange() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), ['min'=>0, 'max'=>1000], 2000);
    }

    public function testRejectsOnlyMaxRange() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), ['max'=>1000], 2000);
    }

    public function testRejectsOnlyMinRange() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), ['min'=>1000], 100);
    }

    public function testRejectsString() {
        $this->expectException(\InvalidArgumentException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), ['min'=>200], 'foo');
    }

    public function testAcceptsMinMaxRange() {
        $this->constraint->validateSingle($this->createMetadataMock(), ['min'=>1900, 'max'=>2100], 2018);
    }

    public function testAcceptsNoRange() {
        $this->constraint->validateSingle($this->createMetadataMock(), [], 1900);
    }

    public function testAcceptsEqualRange() {
        $this->constraint->validateSingle($this->createMetadataMock(), ['min'=>100, 'max'=>100], 100);
    }

    public function testAcceptsOnlyMaxRange() {
        $this->constraint->validateSingle($this->createMetadataMock(), ['max'=>1000], 10);
    }

    public function testAcceptsOnlyMinRange() {
        $this->constraint->validateSingle($this->createMetadataMock(), ['min'=>1900], 2100);
    }
}
