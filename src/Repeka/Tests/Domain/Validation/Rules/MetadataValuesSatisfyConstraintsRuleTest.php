<?php
namespace Repeka\Tests\Domain\Validation\Rules;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Validation\MetadataConstraintProvider;
use Repeka\Domain\Validation\MetadataConstraints\AbstractMetadataConstraint;
use Repeka\Domain\Validation\Rules\MetadataValuesSatisfyConstraintsRule;
use Repeka\Tests\Traits\StubsTrait;

class MetadataValuesSatisfyConstraintsRuleTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testAcceptsWhenThereAreNoConstraints() {
        $constraintlessMetadata = Metadata::create('', '', []);
        $resourceKind = $this->createResourceKindMock($constraintlessMetadata);
        $constraintProvider = $this->createMock(MetadataConstraintProvider::class);
        $constraintProvider->expects($this->never())->method('get');
        $validator = new MetadataValuesSatisfyConstraintsRule($constraintProvider);
        $this->assertTrue($validator->forResourceKind($resourceKind)->validate([0 => 1, 1 => 'test']));
    }

    public function testAcceptsWhenAllRulesAccept() {
        $metadata = Metadata::create('', '', [], [], [], ['constraint1' => 'arg1', 'constraint2' => 'arg2']);
        $resourceKind = $this->createResourceKindMock($metadata);
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
        $metadata = Metadata::create('', '', [], [], [], ['constraint1' => 'arg1', 'constraint2' => 'arg2']);
        $resourceKind = $this->createResourceKindMock($metadata);
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
        $metadata = Metadata::create('', '', [], [], [], ['testConstraint' => 'testArgument']);
        $resourceKind = $this->createResourceKindMock($metadata);
        $validator = new MetadataValuesSatisfyConstraintsRule($constraintProvider);
        $validator->forResourceKind($resourceKind)->validate([0 => 1]);
    }

    private function createResourceKindMock($metadata): \PHPUnit_Framework_MockObject_MockObject {
        $resourceKind = $this->createMock(ResourceKind::class);
        $resourceKind->method('getMetadataByBaseId')->willReturn($metadata);
        return $resourceKind;
    }
}
