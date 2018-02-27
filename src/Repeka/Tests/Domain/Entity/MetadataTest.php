<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;

class MetadataTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingMetadata() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $this->assertEquals(MetadataControl::TEXT(), $metadata->getControl());
        $this->assertEquals('Prop', $metadata->getName());
        $this->assertEquals('AA', $metadata->getLabel()['PL']);
        $this->assertEquals('books', $metadata->getResourceClass());
        $this->assertFalse($metadata->isShownInBrief());
    }

    public function testCannotSetOrdinalNumberLessThan0() {
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
        $metadata->update(['PL' => 'AA1', 'EN' => 'BB'], ['PL' => 'BB'], ['EN' => 'BB'], ['regex' => [null]], true);
        $this->assertEquals(['PL' => 'AA1', 'EN' => 'BB'], $metadata->getLabel());
        $this->assertEquals(['PL' => 'BB'], $metadata->getPlaceholder());
        $this->assertEquals(['EN' => 'BB'], $metadata->getDescription());
        $this->assertEquals(['regex' => [null]], $metadata->getConstraints());
        $this->assertTrue($metadata->isShownInBrief());
    }

    public function testOverridingLabel() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata = $metadata->withOverrides(['label' => ['PL' => 'BB']]);
        $this->assertEquals(['PL' => 'BB'], $metadata->getLabel());
    }

    public function testOverridingDoesNotInfluenceOriginalInstance() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata->withOverrides(['label' => ['PL' => 'BB']]);
        $this->assertEquals(['PL' => 'AA'], $metadata->getLabel());
    }

    public function testDoesNotOverrideWhenBlank() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata = $metadata->withOverrides(['label' => ['PL' => '']]);
        $this->assertEquals(['PL' => 'AA'], $metadata->getLabel());
    }

    public function testChangedMetadataLabelIsLessImportantThanOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata = $metadata->withOverrides(['label' => ['PL' => 'BB']]);
        $metadata->update(['PL' => 'CC'], [], [], [], true);
        $this->assertEquals(['PL' => 'BB'], $metadata->getLabel());
    }

    public function testUpdatingOverridesToTheSameValueClearsOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata = $metadata->withOverrides(['label' => ['PL' => 'AA']]);
        $this->assertEmpty($metadata->getOverrides());
    }

    public function testOverridingDescription() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], ['PL' => 'DescA']);
        $metadata = $metadata->withOverrides(['description' => ['PL' => 'DescB']]);
        $this->assertEquals(['PL' => 'DescB'], $metadata->getDescription());
    }

    public function testAddingALanguageInDescriptionOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], ['PL' => 'DescA']);
        $metadata = $metadata->withOverrides(['description' => ['EN' => 'DescB']]);
        $this->assertEquals(['PL' => 'DescA', 'EN' => 'DescB'], $metadata->getDescription());
    }

    public function testReplacingALanguageInPlaceholderOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], ['PL' => 'DescA', 'EN' => 'DescB']);
        $metadata = $metadata->withOverrides(['placeholder' => ['EN' => 'DescC']]);
        $this->assertEquals(['PL' => 'DescA', 'EN' => 'DescC'], $metadata->getPlaceholder());
    }

    public function testOverridingShownInBrief() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], true);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides(['shownInBrief' => false]);
        $this->assertFalse($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides(['shownInBrief' => true]);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata->update([], [], [], [], false);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides(['shownInBrief' => null]);
        $this->assertFalse($metadata->isShownInBrief());
        $metadata->update([], [], [], [], true);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides([]);
        $this->assertTrue($metadata->isShownInBrief());
    }

    public function testOverriddenShownInBriefIsNotPresentInOverrides() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], true);
        $metadata = $metadata->withOverrides(['shownInBrief' => true]);
        $this->assertArrayNotHasKey('shownInBrief', $metadata->getOverrides());
    }

    public function testOverridingConstraints() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], ['regex' => 'abc']);
        $this->assertEquals(['regex' => 'abc'], $metadata->getConstraints());
        $metadata = $metadata->withOverrides(['constraints' => ['regex' => 'cab']]);
        $this->assertEquals(['regex' => 'cab'], $metadata->getConstraints());
    }

    public function testAddingConstraintWithOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], ['regex' => 'abc']);
        $metadata = $metadata->withOverrides(['constraints' => ['count' => 2]]);
        $this->assertEquals(['regex' => 'abc', 'count' => 2], $metadata->getConstraints());
    }

    public function testClearingConstraintWithOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], ['regex' => 'abc']);
        $metadata = $metadata->withOverrides(['constraints' => ['regex' => null]]);
        $this->assertEquals(['regex' => null], $metadata->getConstraints());
    }

    public function testOverridingZeroConstraintWithNull() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], ['maxCount' => 0]);
        $this->assertEquals(['maxCount' => 0], $metadata->getConstraints());
        $metadata = $metadata->withOverrides(['constraints' => ['maxCount' => null]]);
        $this->assertEmpty($metadata->getOverrides());
        $this->assertEquals(['maxCount' => 0], $metadata->getConstraints());
    }

    public function testParentIdNullIfNoParent() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $this->assertNull($metadata->getParentId());
    }

    public function testExceptionWhenGettingParentOfTopLevelMetadata() {
        $this->expectException(InvalidArgumentException::class);
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $metadata->getParent();
    }

    public function testTopLevelByParent() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $this->assertTrue($metadata->isTopLevel());
        $metadata->setParent($metadata);
        $this->assertFalse($metadata->isTopLevel());
    }
}
