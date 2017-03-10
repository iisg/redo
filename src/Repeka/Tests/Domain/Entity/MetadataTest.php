<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;

class MetadataTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingMetadata() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA']);
        $this->assertEquals('text', $metadata->getControl());
        $this->assertEquals('Prop', $metadata->getName());
        $this->assertEquals('AA', $metadata->getLabel()['PL']);
    }

    public function testCreatingForResourceKind() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertSame($rk, $childMetadata->getResourceKind());
    }

    public function testGettingControlAndNameOfBaseMetadata() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertEquals('text', $childMetadata->getControl());
        $this->assertEquals('Prop', $childMetadata->getName());
    }

    public function testExtendingLanguageValues() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
        $this->assertEquals('EN', $childMetadata->getLabel()['EN']);
    }

    public function testOverridingLanguageValues() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => 'Another'], $rk, $baseMetadata);
        $this->assertEquals('Another', $childMetadata->getLabel()['PL']);
    }

    public function testDoesNotOverrideWhenEmpty() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testDoesNotOverrideWhenBlank() {
        $rk = new ResourceKind([]);
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => '   '], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testSettingOrdinalNumberLessThan0() {
        $this->expectException(InvalidArgumentException::class);
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA']);
        $metadata->updateOrdinalNumber(-1);
    }

    public function testSettingOrdinalNumberTo0() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA']);
        $metadata->updateOrdinalNumber(0);
    }

    public function testUpdating() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], ['PL' => 'AA'], ['PL' => 'AA']);
        $metadata->update(['PL' => 'AA1', 'EN' => 'BB'], ['PL' => 'BB'], ['EN' => 'BB'], $metadata->getConstraints());
        $this->assertEquals(['PL' => 'AA1', 'EN' => 'BB'], $metadata->getLabel());
        $this->assertEquals(['PL' => 'BB'], $metadata->getPlaceholder());
        $this->assertEquals(['EN' => 'BB'], $metadata->getDescription());
    }

    /**
     * Consider this scenario:
     * 1. A base metadata (BM) is created with some label.
     * 2. A resource kind metadata (RM) is created with BM as base and inherited label.
     *    At this point $RM->getLabel() will return label inherited from BM.
     * 3. Resource kind is updated. This causes all its metadata to be recreated with identical values.
     *    RM will either (1) be assigned inherited label, or (2) will keep inheriting it. In both cases $RM->getLabel() will return
     *    identical value, but in case 1 editing BM's label won't affect RM's label.
     * This test makes sure that RM still has its own label empty and will inherit it from BM (so editing BM's label changes RM's label).
     */
    public function testUpdatingWithValuesSameAsInBaseCausesInheritance() {
        $original = ['PL' => 'Original'];
        $changed = ['PL' => 'Changed'];
        $base = Metadata::create('text', 'test', $original, $original, $original, [0]);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update($metadata->getLabel(), $metadata->getPlaceholder(), $metadata->getDescription(), $metadata->getConstraints());
        $base->update($changed, $changed, $changed, [1]);
        $this->assertEquals($changed, $metadata->getLabel());
        $this->assertEquals($changed, $metadata->getPlaceholder());
        $this->assertEquals($changed, $metadata->getDescription());
        $this->assertEquals([1], $metadata->getConstraints());
    }

    public function testDoesNotUpdateMissingValuesInLabel() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], ['PL' => 'AA'], ['PL' => 'AA']);
        $metadata->update(['EN' => 'BB'], [], [], $metadata->getConstraints());
        $this->assertEquals(['PL' => 'AA', 'EN' => 'BB'], $metadata->getLabel());
    }

    public function testConcreteConstraintsOverrideBase() {
        $initialResourceKindIds = [100];
        $overridingResourceKindIds = [101];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('relationship', 'base', [], [], [], ['resourceKind' => $initialResourceKindIds]);
        // @codingStandardsIgnoreStart
        $concreteMetadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, [], [],
            ['resourceKind' => $overridingResourceKindIds]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals($overridingResourceKindIds, $concreteMetadata->getConstraints()['resourceKind']);
    }

    public function testEmptyConcreteConstraintsOverrideBase() {
        $initialResourceKindIds = [100];
        $overridingResourceKindIds = [];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('relationship', 'base', [], [], [], ['resourceKind' => $initialResourceKindIds]);
        // @codingStandardsIgnoreStart
        $concreteMetadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, [], [],
            ['resourceKind' => $overridingResourceKindIds]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals($overridingResourceKindIds, $concreteMetadata->getConstraints()['resourceKind']);
    }

    public function testMissingConcreteConstraintsFallbackToBase() {
        $initialResourceKindIds = [100];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('relationship', 'base', [], [], [], ['resourceKind' => $initialResourceKindIds]);
        $concreteMetadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, [], [], [/* nothing here */]);
        $this->assertEquals($initialResourceKindIds, $concreteMetadata->getConstraints()['resourceKind']);
    }
}
