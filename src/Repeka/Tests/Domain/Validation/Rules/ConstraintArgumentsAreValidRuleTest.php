<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\MetadataConstraintProvider;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Tests\Traits\StubsTrait;

class ConstraintArgumentsAreValidRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testAcceptsWhenAllValidatorsAccept() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->exactly(2))->method('validateArgument')->willReturn(true);
        $constraintProvider = $this->createMetadataConstraintProviderStub([
            'a' => $constraint,
            'b' => $constraint,
        ]);
        $this->assertTrue((new ConstraintArgumentsAreValidRule($constraintProvider))->validate([
            'a' => 1,
            'b' => 2,
        ]));
    }

    public function testRejectsWhenAnyValidatorRejects() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->exactly(2))->method('validateArgument')->willReturn(true, false, true);
        $constraintProvider = $this->createMetadataConstraintProviderStub([
            'a' => $constraint,
            'b' => $constraint,
            'c' => $constraint,
        ]);
        $this->assertFalse((new ConstraintArgumentsAreValidRule($constraintProvider))->validate([
            'a' => 1,
            'b' => 2,
            'c' => 3,
        ]));
    }

    public function testAcceptsWhenNothingToValidate() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->never())->method('validateArgument')->willReturn(true);
        $constraintProvider = $this->createMetadataConstraintProviderStub([
            'a' => $constraint,
            'b' => $constraint,
        ]);
        $this->assertTrue((new ConstraintArgumentsAreValidRule($constraintProvider))->validate([]));
    }

    public function testFailsWhenValidatorDoesNotExist() {
        $constraintProvider = $this->createMock(MetadataConstraintProvider::class);
        $constraintProvider->method('get')->willThrowException(new \InvalidArgumentException());
        $this->assertFalse((new ConstraintArgumentsAreValidRule($constraintProvider))->validate(['abc' => 123]));
    }
}
