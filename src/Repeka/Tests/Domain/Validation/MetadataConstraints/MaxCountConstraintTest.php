<?php
namespace Repeka\Tests\Domain\Validation\MetadataConstraints;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\InvalidCommandException;
use Repeka\Domain\Validation\MetadataConstraints\MaxCountConstraint;
use Repeka\Tests\Traits\StubsTrait;

class MaxCountConstraintTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  MaxCountConstraint */
    private $constraint;
    /** @var ResourceEntity|\PHPUnit_Framework_MockObject_MockObject */
    private $resource;

    protected function setUp() {
        $this->constraint = new MaxCountConstraint();
        $this->resource = $this->createResourceMock(1);
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
        $this->constraint->validateSingle($this->createMetadataMockWithMaxCountConstraint(0), null, $this->resource);
    }

    public function testAcceptsNotGreater() {
        $max = 5;
        for ($count = 0; $count <= $max; $count++) {
            $values = array_fill(0, $count, null);
            $this->constraint->validateAll($this->createMetadataMockWithMaxCountConstraint($max), $values, $this->resource);
        }
    }

    public function testRejectsGreater() {
        $this->expectException(InvalidCommandException::class);
        $max = 5;
        $this->constraint->validateAll($this->createMetadataMockWithMaxCountConstraint($max), array_fill(0, 6, null), $this->resource);
    }

    public function testAcceptsWhenNoConstraintConfig() {
        $this->constraint->validateAll($this->createMetadataMock(), array_fill(0, 10, null), $this->resource);
    }

    public function testTreatsNullAsInfinity() {
        $hugeArray = array_fill(0, 10000, null);
        $this->constraint->validateAll($this->createMetadataMockWithMaxCountConstraint(null), $hugeArray, $this->resource);
    }

    public function testTreatsMinusOneAsInfinity() {
        $hugeArray = array_fill(0, 10000, null);
        $this->constraint->validateAll($this->createMetadataMockWithMaxCountConstraint(-1), $hugeArray, $this->resource);
    }

    private function createMetadataMockWithMaxCountConstraint($maxCount) {
        return $this->createMetadataMock(1, null, null, ['maxCount' => $maxCount]);
    }
}
