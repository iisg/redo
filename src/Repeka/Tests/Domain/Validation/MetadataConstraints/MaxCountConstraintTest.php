<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Validation\MetadataConstraints\MaxCountConstraint;
use Repeka\Tests\Traits\StubsTrait;

class MaxCountConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  MaxCountConstraint */
    private $constraint;

    protected function setUp() {
        $this->constraint = new MaxCountConstraint();
    }

    public function testAcceptsValidConfig() {
        $this->assertTrue($this->constraint->isConfigValid(1));
        $this->assertTrue($this->constraint->isConfigValid(3));
        $this->assertTrue($this->constraint->isConfigValid(123));
        $this->assertTrue($this->constraint->isConfigValid(null));
        $this->assertTrue($this->constraint->isConfigValid(-1));
    }

    public function testRejectsInvalidConfig() {
        $this->assertFalse($this->constraint->isConfigValid([]));
        $this->assertFalse($this->constraint->isConfigValid(['max' => 100]));
        $this->assertFalse($this->constraint->isConfigValid('1'));
        $this->assertFalse($this->constraint->isConfigValid('0'));
        $this->assertFalse($this->constraint->isConfigValid(0));
        $this->assertFalse($this->constraint->isConfigValid(-2));
    }

    public function testRejectsSingleValidateCall() {
        $this->expectException(\BadMethodCallException::class);
        $this->constraint->validateSingle($this->createMetadataMock(), 0, null);
    }

    public function testAcceptsNotGreater() {
        $max = 5;
        for ($count = 0; $count <= $max; $count++) {
            $values = array_fill(0, $count, null);
            $this->constraint->validateAll($this->createMetadataMock(), $max, $values);
        }
    }

    public function testRejectsGreater() {
        $this->expectException(InvalidCommandException::class);
        $max = 5;
        $this->constraint->validateAll($this->createMetadataMock(), $max, array_fill(0, 6, null));
    }

    public function testTreatsNullAsInfinity() {
        $hugeArray = array_fill(0, 10000, null);
        $this->constraint->validateAll($this->createMetadataMock(), null, $hugeArray);
    }

    public function testTreatsMinusOneAsInfinity() {
        $hugeArray = array_fill(0, 10000, null);
        $this->constraint->validateAll($this->createMetadataMock(), -1, $hugeArray);
    }
}
