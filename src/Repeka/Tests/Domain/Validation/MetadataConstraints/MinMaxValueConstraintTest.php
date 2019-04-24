<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Validation\MetadataConstraints\MinMaxValueConstraint;
use Repeka\Tests\Traits\StubsTrait;

class MinMaxValueConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  MinMaxValueConstraint */
    private $constraint;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    protected function setUp() {
        $this->constraint = new MinMaxValueConstraint();
        $this->resource = $this->createResourceMock(1);
    }

    public function testAcceptsValidConfig() {
        $this->assertTrue($this->constraint->isConfigValid(['min' => 0, 'max' => 100]));
        $this->assertTrue($this->constraint->isConfigValid(['max' => 2000]));
        $this->assertTrue($this->constraint->isConfigValid(['min' => 1900]));
        $this->assertTrue($this->constraint->isConfigValid([]));
    }

    public function testRejectsInvalidConfig() {
        $this->assertFalse($this->constraint->isConfigValid(['min' => 2000, 'max' => 10]));
        $this->assertFalse($this->constraint->isConfigValid(['min' => 0, 'max' => 100, 'foo' => 1000]));
        $this->assertFalse($this->constraint->isConfigValid(['min' => 'abc', 'max=' > 100]));
    }

    public function testRejectsMinMaxRange() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle(
            $this->createMetadataMockWithMinMaxConstraint(['min' => 0, 'max' => 1000]),
            2000,
            $this->resource
        );
    }

    public function testRejectsOnlyMaxRange() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMockWithMinMaxConstraint(['max' => 1000]), 2000, $this->resource);
    }

    public function testRejectsOnlyMinRange() {
        $this->expectException(\DomainException::class);
        $this->constraint->validateSingle($this->createMetadataMockWithMinMaxConstraint(['min' => 1000]), 100, $this->resource);
    }

    public function testRejectsString() {
        $this->expectException(\InvalidArgumentException::class);
        $this->constraint->validateSingle($this->createMetadataMockWithMinMaxConstraint(['min' => 200]), 'foo', $this->resource);
    }

    public function testAcceptsMinMaxRange() {
        $this->constraint->validateSingle(
            $this->createMetadataMockWithMinMaxConstraint(['min' => 1900, 'max' => 2100]),
            2018,
            $this->resource
        );
    }

    public function testAcceptsNoRange() {
        $this->constraint->validateSingle($this->createMetadataMockWithMinMaxConstraint([]), 1900, $this->resource);
    }

    public function testAcceptsEqualRange() {
        $this->constraint->validateSingle(
            $this->createMetadataMockWithMinMaxConstraint(['min' => 100, 'max' => 100]),
            100,
            $this->resource
        );
    }

    public function testAcceptsOnlyMaxRange() {
        $this->constraint->validateSingle($this->createMetadataMockWithMinMaxConstraint(['max' => 1000]), 10, $this->resource);
    }

    public function testAcceptsOnlyMinRange() {
        $this->constraint->validateSingle($this->createMetadataMockWithMinMaxConstraint(['min' => 1900]), 2100, $this->resource);
    }

    public function testAcceptsWhenNoConstraintConfig() {
        $this->constraint->validateSingle($this->createMetadataMock(), 2100, $this->resource);
    }

    private function createMetadataMockWithMinMaxConstraint(array $constraint) {
        return $this->createMetadataMock(1, null, null, ['minMaxValue' => $constraint]);
    }
}
