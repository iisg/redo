<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Validation\MetadataConstraintManager;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\Rules\ConstraintArgumentsAreValidRule;
use Repeka\Tests\Traits\StubsTrait;

class ConstraintArgumentsAreValidRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testAcceptsWhenAllValidatorsAccept() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->exactly(2))->method('isConfigValid')->willReturn(true);
        $constraintManager = $this->createMetadataConstraintManagerStub(
            [
                'a' => $constraint,
                'b' => $constraint,
            ]
        );
        $this->assertTrue(
            (new ConstraintArgumentsAreValidRule($constraintManager))->validate(
                [
                    'a' => 1,
                    'b' => 2,
                ]
            )
        );
    }

    public function testRejectsWhenAnyValidatorRejects() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->exactly(2))->method('isConfigValid')->willReturn(true, false, true);
        $constraintManager = $this->createMetadataConstraintManagerStub(
            [
                'a' => $constraint,
                'b' => $constraint,
                'c' => $constraint,
            ]
        );
        $this->assertFalse(
            (new ConstraintArgumentsAreValidRule($constraintManager))->validate(
                [
                    'a' => 1,
                    'b' => 2,
                    'c' => 3,
                ]
            )
        );
    }

    public function testAcceptsWhenNothingToValidate() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->never())->method('isConfigValid')->willReturn(true);
        $constraintManager = $this->createMetadataConstraintManagerStub(
            [
                'a' => $constraint,
                'b' => $constraint,
            ]
        );
        $this->assertTrue((new ConstraintArgumentsAreValidRule($constraintManager))->validate([]));
    }

    public function testFailsWhenValidatorDoesNotExist() {
        $constraintManager = $this->createMock(MetadataConstraintManager::class);
        $constraintManager->method('get')->willThrowException(new \InvalidArgumentException());
        $this->assertFalse((new ConstraintArgumentsAreValidRule($constraintManager))->validate(['abc' => 123]));
    }

    public function testRejectsWhenValidatorThrows() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->method('isConfigValid')
            ->willThrowException(new \InvalidArgumentException('UNICORNS, UNICORNS EVERYWHERE'));
        $constraintManager = $this->createMetadataConstraintManagerStub(['a' => $constraint]);
        $rule = new ConstraintArgumentsAreValidRule($constraintManager);
        $this->assertFalse($rule->validate(['a' => 1]));
        try {
            $rule->assert(['a' => 1]);
        } catch (\Exception $e) {
            $this->assertEquals($e->getParam('error'), "UNICORNS, UNICORNS EVERYWHERE");
        }
    }
}
