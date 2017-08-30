<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Assert\AssertionFailedException;
use Repeka\Domain\Validation\MetadataConstraints\MaxCountConstraint;

class MaxCountConstraintTest extends \PHPUnit_Framework_TestCase {
    /** @var  MaxCountConstraint */
    private $constraint;

    protected function setUp() {
        $this->constraint = new MaxCountConstraint();
    }

    public function testAcceptsValidConfig() {
        $this->assertTrue($this->constraint->isConfigValid(1));
        $this->assertTrue($this->constraint->isConfigValid(3));
        $this->assertTrue($this->constraint->isConfigValid(123));
        $this->assertTrue($this->constraint->isConfigValid(0));
    }

    public function testRejectsInvalidConfig() {
        $this->assertFalse($this->constraint->isConfigValid([]));
        $this->assertFalse($this->constraint->isConfigValid(['max' => 100]));
        $this->assertFalse($this->constraint->isConfigValid('1'));
        $this->assertFalse($this->constraint->isConfigValid('0'));
        $this->assertFalse($this->constraint->isConfigValid(-1));
    }

    public function testRejectsNonArrayValues() {
        $this->expectException(AssertionFailedException::class);
        $this->assertFalse($this->constraint->isValueValid(0, null));
    }

    public function testAcceptsNotGreater() {
        $max = 5;
        for ($count = 0; $count <= $max; $count++) {
            $values = array_fill(0, $count, null);
            $this->assertTrue($this->constraint->isValueValid($max, $values));
        }
    }

    public function testRejectsGreater() {
        $max = 5;
        $this->assertFalse($this->constraint->isValueValid($max, array_fill(0, 6, null)));
        $this->assertFalse($this->constraint->isValueValid($max, array_fill(0, 7, null)));
        $this->assertFalse($this->constraint->isValueValid($max, array_fill(0, 100, null)));
    }

    public function testTreatsZeroAsInfinity() {
        $hugeArray = array_fill(0, 100000, null);
        $this->assertTrue($this->constraint->isValueValid(0, $hugeArray));
    }
}
