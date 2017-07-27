<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;

class MetadataTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingMetadata() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], 'books');
        $this->assertEquals('text', $metadata->getControl());
        $this->assertEquals('Prop', $metadata->getName());
        $this->assertEquals('AA', $metadata->getLabel()['PL']);
        $this->assertEquals('books', $metadata->getResourceClass());
        $this->assertFalse($metadata->isShownInBrief());
    }

    public function testCreatingForResourceKind() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL'], 'books');
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata, 'books');
        $this->assertSame($rk, $childMetadata->getResourceKind());
    }

    public function testGettingControlAndNameOfBaseMetadata() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL'], 'books');
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata, 'books');
        $this->assertEquals('text', $childMetadata->getControl());
        $this->assertEquals('Prop', $childMetadata->getName());
    }

    public function testExtendingLanguageValues() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL'], 'books');
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata, 'books');
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
        $this->assertEquals('EN', $childMetadata->getLabel()['EN']);
    }

    public function testOverridingLanguageValues() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL'], 'books');
        $childMetadata = Metadata::createForResourceKind(['PL' => 'Another'], $rk, $baseMetadata, 'books');
        $this->assertEquals('Another', $childMetadata->getLabel()['PL']);
    }

    public function testOverridingShownInBriefValue() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => ''], 'books', [], [], [], false);
        $childMetadata1 = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata, 'books', [], [], [], false);
        $childMetadata2 = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata, 'books', [], [], [], true);
        $this->assertEquals($baseMetadata->isShownInBrief(), $childMetadata1->isShownInBrief());
        $this->assertTrue($childMetadata2->isShownInBrief());
    }

    public function testDoesNotOverrideWhenEmpty() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL'], 'books');
        $childMetadata = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata, 'books');
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testDoesNotOverrideWhenBlank() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('text', 'Prop', ['PL' => 'PL'], 'books');
        $childMetadata = Metadata::createForResourceKind(['PL' => '   '], $rk, $baseMetadata, 'books');
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testSettingOrdinalNumberLessThan0() {
        $this->expectException(InvalidArgumentException::class);
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], 'books');
        $metadata->updateOrdinalNumber(-1);
    }

    public function testSettingOrdinalNumberTo0() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], 'books');
        $metadata->updateOrdinalNumber(0);
    }

    public function testUpdating() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], 'books', ['PL' => 'AA'], ['PL' => 'AA'], [], false);
        $metadata->update(['PL' => 'AA1', 'EN' => 'BB'], ['PL' => 'BB'], ['EN' => 'BB'], [1 => [null]], true);
        $this->assertEquals(['PL' => 'AA1', 'EN' => 'BB'], $metadata->getLabel());
        $this->assertEquals(['PL' => 'BB'], $metadata->getPlaceholder());
        $this->assertEquals(['EN' => 'BB'], $metadata->getDescription());
        $this->assertEquals([1 => [null]], $metadata->getConstraints());
        $this->assertTrue($metadata->isShownInBrief());
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
        $base = Metadata::create('text', 'test', $original, 'books', $original, $original, [0], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base, 'books');
        $metadata->update(
            $metadata->getLabel(),
            $metadata->getPlaceholder(),
            $metadata->getDescription(),
            $metadata->getConstraints(),
            false
        );
        $base->update($changed, $changed, $changed, [1], true);
        $this->assertEquals($changed, $metadata->getLabel());
        $this->assertEquals($changed, $metadata->getPlaceholder());
        $this->assertEquals($changed, $metadata->getDescription());
        $this->assertEquals([1], $metadata->getConstraints());
        $this->assertTrue($metadata->isShownInBrief());
    }

    public function testDoesNotUpdateMissingValuesInLabel() {
        $metadata = Metadata::create('text', 'Prop', ['PL' => 'AA'], 'books', ['PL' => 'AA'], ['PL' => 'AA']);
        $metadata->update(['EN' => 'BB'], [], [], [], false);
        $this->assertEquals(['PL' => 'AA', 'EN' => 'BB'], $metadata->getLabel());
    }

    public function testConcreteConstraintsOverrideBase() {
        $initialResourceKindIds = [100];
        $overridingResourceKindIds = [101];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('relationship', 'base', [], 'books', [], [], ['resourceKind' => $initialResourceKindIds]);
        // @codingStandardsIgnoreStart
        $concreteMetadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, 'books', [], [],
            ['resourceKind' => $overridingResourceKindIds]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals($overridingResourceKindIds, $concreteMetadata->getConstraints()['resourceKind']);
    }

    public function testEmptyConcreteConstraintsOverrideBase() {
        $initialResourceKindIds = [100];
        $overridingResourceKindIds = [];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('relationship', 'base', [], 'books', [], [], ['resourceKind' => $initialResourceKindIds]);
        // @codingStandardsIgnoreStart
        $concreteMetadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, 'books', [], [],
            ['resourceKind' => $overridingResourceKindIds]);
        // @codingStandardsIgnoreEnd
        $this->assertEquals($overridingResourceKindIds, $concreteMetadata->getConstraints()['resourceKind']);
    }

    public function testMissingConcreteConstraintsFallbackToBase() {
        $initialResourceKindIds = [100];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('relationship', 'base', [], 'books', [], [], ['resourceKind' => $initialResourceKindIds]);
        $concreteMetadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, 'books', [], [], [/* nothing here */]);
        $this->assertEquals($initialResourceKindIds, $concreteMetadata->getConstraints()['resourceKind']);
    }
}
