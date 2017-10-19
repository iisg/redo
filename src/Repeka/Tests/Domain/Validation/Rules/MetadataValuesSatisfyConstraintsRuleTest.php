<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\MetadataConstraintProvider;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataValuesSatisfyConstraintsRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testAcceptsWhenThereAreNoConstraints() {
        $constraintlessMetadata = Metadata::create(MetadataControl::TEXTAREA(), '', [], 'books');
        $resourceKind = $this->createSingleMetadataResourceKindMock($constraintlessMetadata);
        $constraintProvider = $this->createMock(MetadataConstraintProvider::class);
        $constraintProvider->expects($this->never())->method('get');
        $validator = new MetadataValuesSatisfyConstraintsRule($constraintProvider);
        $this->assertTrue($validator->forResourceKind($resourceKind)->validate([0 => 1, 1 => 'test']));
    }

    public function testAcceptsWhenAllRulesAccept() {
        $constraints = ['constraint1' => 'arg1', 'constraint2' => 'arg2'];
        $metadata = Metadata::create(MetadataControl::TEXTAREA(), '', [], 'books', [], [], $constraints);
        $resourceKind = $this->createSingleMetadataResourceKindMock($metadata);
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->exactly(4))->method('validateValue')->willReturn(true);
        $constraintProvider = $this->createMetadataConstraintProviderStub([
            'constraint1' => $constraint,
            'constraint2' => $constraint,
        ]);
        $validator = new MetadataValuesSatisfyConstraintsRule($constraintProvider);
        $this->assertTrue($validator->forResourceKind($resourceKind)->validate([0 => 'value1', 1 => 'value2']));
    }

    public function testRejectsWhenAnyRuleRejects() {
        $constraints = ['constraint1' => 'arg1', 'constraint2' => 'arg2'];
        $metadata = Metadata::create(MetadataControl::TEXTAREA(), '', [], 'books', [], [], $constraints);
        $resourceKind = $this->createSingleMetadataResourceKindMock($metadata);
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraint->expects($this->exactly(3))->method('validateValue')->willReturnOnConsecutiveCalls(true, true, false, true);
        $constraintProvider = $this->createMetadataConstraintProviderStub([
            'constraint1' => $constraint,
            'constraint2' => $constraint,
        ]);
        $validator = new MetadataValuesSatisfyConstraintsRule($constraintProvider);
        $this->assertFalse($validator->forResourceKind($resourceKind)->validate([0 => 'value1', 1 => 'value2']));
    }

    public function testUsesCorrectRule() {
        $constraint = $this->createMock(AbstractMetadataConstraint::class);
        $constraintProvider = $this->createMock(MetadataConstraintProvider::class);
        $constraintProvider->expects($this->once())->method('get')
            ->with('testConstraint')
            ->willReturn($constraint);
        $metadata = Metadata::create(MetadataControl::TEXTAREA(), '', [], 'books', [], [], ['testConstraint' => 'testArgument']);
        $resourceKind = $this->createSingleMetadataResourceKindMock($metadata);
        $validator = new MetadataValuesSatisfyConstraintsRule($constraintProvider);
        $validator->forResourceKind($resourceKind)->validate([0 => 1]);
    }

    private function createSingleMetadataResourceKindMock($metadata): \PHPUnit_Framework_MockObject_MockObject {
        $resourceKind = $this->createMock(ResourceKind::class);
        $resourceKind->method('getMetadataByBaseId')->willReturn($metadata);
        return $resourceKind;
    }
}
