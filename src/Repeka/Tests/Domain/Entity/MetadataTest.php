<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;

class MetadataTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingMetadata() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $this->assertEquals(MetadataControl::TEXT(), $metadata->getControl());
        $this->assertEquals('Prop', $metadata->getName());
        $this->assertEquals('AA', $metadata->getLabel()['PL']);
        $this->assertEquals('books', $metadata->getResourceClass());
        $this->assertFalse($metadata->isShownInBrief());
    }

    public function testCreatingForResourceKind() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertSame($rk, $childMetadata->getResourceKind());
    }

    public function testGettingControlAndNameOfBaseMetadata() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertEquals(MetadataControl::TEXT(), $childMetadata->getControl());
        $this->assertEquals('Prop', $childMetadata->getName());
    }

    public function testExtendingLanguageValues() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['EN' => 'EN'], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
        $this->assertEquals('EN', $childMetadata->getLabel()['EN']);
    }

    public function testOverridingLanguageValues() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => 'Another'], $rk, $baseMetadata);
        $this->assertEquals('Another', $childMetadata->getLabel()['PL']);
    }

    public function testOverridingShownInBriefValue() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => ''], [], [], [], false);
        $childMetadata1 = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata, [], [], [], false);
        $childMetadata2 = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata, [], [], [], true);
        $this->assertEquals($baseMetadata->isShownInBrief(), $childMetadata1->isShownInBrief());
        $this->assertTrue($childMetadata2->isShownInBrief());
    }

    public function testDoesNotOverrideWhenEmpty() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => ''], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testDoesNotOverrideWhenBlank() {
        $rk = new ResourceKind([], 'books');
        $baseMetadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'PL']);
        $childMetadata = Metadata::createForResourceKind(['PL' => '   '], $rk, $baseMetadata);
        $this->assertEquals('PL', $childMetadata->getLabel()['PL']);
    }

    public function testSettingOrdinalNumberLessThan0() {
        $this->expectException(InvalidArgumentException::class);
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata->updateOrdinalNumber(-1);
    }

    public function testSettingOrdinalNumberTo0() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata->updateOrdinalNumber(0);
    }

    public function testUpdating() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA'], ['PL' => 'AA'], ['PL' => 'AA'], [], false);
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
        $base = Metadata::create('books', MetadataControl::TEXT(), 'test', $original, $original, $original, ['test' => 0], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update(
            $metadata->getLabel(),
            $metadata->getPlaceholder(),
            $metadata->getDescription(),
            $metadata->getConstraints(),
            false
        );
        $base->update($changed, $changed, $changed, ['test' => 1], true);
        $this->assertEquals($changed, $metadata->getLabel());
        $this->assertEquals($changed, $metadata->getPlaceholder());
        $this->assertEquals($changed, $metadata->getDescription());
        $this->assertEquals(['test' => 1], $metadata->getConstraints());
        $this->assertTrue($metadata->isShownInBrief());
    }

    /**
     * Similar to previous one, but checks if overriding some constraints and inheriting others works properly.
     */
    public function testPartialConstraintOverriding() {
        // values don't matter, they just have to be different and non-null
        $initialValueA = 'a';
        $otherValueA = 'a2';
        $initialValueB = 'b';
        $overrideValueB = '!B!';
        $otherValueB = 'b2';
        $base = Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $initialValueB,
        ], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update([], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $overrideValueB,
        ], false);
        $base->update([], [], [], [
            'keyA' => $otherValueA,
            'keyB' => $otherValueB,
        ], false);
        $constraints = $metadata->getConstraints();
        $this->assertCount(2, $constraints);
        $this->assertEquals($otherValueA, $constraints['keyA']);
        $this->assertEquals($overrideValueB, $constraints['keyB']);
    }

    public function testExtraConstraintsAreRemovedWhenMissingInUpdate() {
        $initialValueA = 'a';
        $initialValueB = 'b';
        $otherValueA = '!A!';
        $otherValueB = '!B!';
        $base = Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $initialValueB,
        ], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update([], [], [], [
            'keyA' => $initialValueA,
        ], false);
        $base->update([], [], [], [
            'keyA' => $otherValueA,
            'keyB' => $otherValueB,
        ], false);
        $constraints = $metadata->getConstraints();
        $this->assertCount(1, $constraints);
        $this->assertEquals($otherValueA, $constraints['keyA']);
    }

    public function testConstraintsAddedToBaseArePresent() {
        $initialValueA = 'a';
        $otherValueA = '!A!';
        $valueB = '!B!';
        $base = Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'keyA' => $initialValueA,
        ], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update([], [], [], [
            'keyA' => $initialValueA,
        ], false);
        $base->update([], [], [], [
            'keyA' => $otherValueA,
            'keyB' => $valueB,
        ], false);
        $constraints = $metadata->getConstraints();
        $this->assertCount(2, $constraints);
        $this->assertEquals($otherValueA, $constraints['keyA']);
        $this->assertEquals($valueB, $constraints['keyB']);
    }

    public function testInheritedConstraintsAreRemovedWhenRemovedFromBase() {
        $initialValueA = 'a';
        $initialValueB = 'b';
        $otherValueA = '!A!';
        $base = Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $initialValueB,
        ], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update([], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $initialValueB,
        ], false);
        $base->update([], [], [], [
            'keyA' => $otherValueA,
        ], false);
        $constraints = $metadata->getConstraints();
        $this->assertCount(1, $constraints);
        $this->assertEquals($otherValueA, $constraints['keyA']);
    }

    public function testOverriddenConstraintsArePreservedWhenRemovedFromBase() {
        $initialValueA = 'a';
        $initialValueB = 'b';
        $overrideValueB = '~~b~~';
        $otherValueA = '!A!';
        $otherValueB = '!B!';
        $base = Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $initialValueB,
        ], false);
        $resourceKind = $this->createMock(ResourceKind::class);
        $metadata = Metadata::createForResourceKind([], $resourceKind, $base);
        $metadata->update([], [], [], [
            'keyA' => $initialValueA,
            'keyB' => $overrideValueB,
        ], false);
        $base->update([], [], [], [
            'keyA' => $otherValueA,
            'keyB' => $otherValueB,
        ], false);
        $constraints = $metadata->getConstraints();
        $this->assertCount(2, $constraints);
        $this->assertEquals($otherValueA, $constraints['keyA']);
        $this->assertEquals($overrideValueB, $constraints['keyB']);
    }

    public function testRejectsNullValueForConstraints() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/null/');
        Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'test' => null,  // nulls are reserved for internal use: null means inheritance
        ], false);
    }

    public function testRejectsNullValueForConstraintUpdates() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('/null/');
        $metadata = Metadata::create('', MetadataControl::TEXT(), 'test', [], [], [], [
            'test' => true,
        ], false);
        $metadata->update([], [], [], [
            'test' => null,
        ], false);
    }

    public function testConstraintsOverrideBase() {
        $initialValue = [100];
        $overrideValue = [101];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'base', [], [], [], ['resourceKind' => $initialValue]);
        $overrideConstraints = ['resourceKind' => $overrideValue];
        $metadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, [], [], $overrideConstraints);
        $this->assertEquals($overrideValue, $metadata->getConstraints()['resourceKind']);
    }

    public function testEmptyConstraintsOverrideBase() {
        $initialValue = [100];
        $overrideValue = [];
        /** @var ResourceKind $dummyResourceKind */
        $dummyResourceKind = $this->createMock(ResourceKind::class);
        $baseMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'base', [], [], [], ['resourceKind' => $initialValue]);
        $overrideConstraints = ['resourceKind' => $overrideValue];
        $metadata = Metadata::createForResourceKind([], $dummyResourceKind, $baseMetadata, [], [], $overrideConstraints);
        $this->assertEquals($overrideValue, $metadata->getConstraints()['resourceKind']);
    }
}
