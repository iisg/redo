<?php
namespace Repeka\Tests\Domain\Entity;

use Assert\InvalidArgumentException;
use Repeka\Domain\Constants\SystemResourceClass;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Tests\Traits\StubsTrait;

class MetadataTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    public function testCreatingMetadata() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', ['PL' => 'AA']);
        $this->assertEquals(MetadataControl::TEXT(), $metadata->getControl());
        $this->assertEquals('prop', $metadata->getName());
        $this->assertEquals('AA', $metadata->getLabel()['PL']);
        $this->assertEquals('books', $metadata->getResourceClass());
        $this->assertFalse($metadata->isShownInBrief());
        $this->assertFalse($metadata->isCopiedToChildResource());
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

    public function testTrimsTheName() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'prop  ', ['PL' => 'AA']);
        $this->assertEquals('prop', $metadata->getName());
    }

    public function testUpdating() {
        $metadata = Metadata::create(
            'books',
            MetadataControl::TEXT(),
            'Prop',
            ['PL' => 'AA'],
            ['PL' => 'AA'],
            ['PL' => 'AA'],
            [],
            'oldGroup',
            null,
            false,
            false
        );
        $metadata->update(['PL' => 'AA1', 'EN' => 'BB'], ['PL' => 'BB'], ['EN' => 'BB'], ['regex' => [null]], 'newGroup', null, true, true);
        $this->assertEquals(['PL' => 'AA1', 'EN' => 'BB'], $metadata->getLabel());
        $this->assertEquals(['PL' => 'BB'], $metadata->getPlaceholder());
        $this->assertEquals(['EN' => 'BB'], $metadata->getDescription());
        $this->assertEquals(['regex' => [null]], $metadata->getConstraints());
        $this->assertEquals('newGroup', $metadata->getGroupId());
        $this->assertTrue($metadata->isShownInBrief());
        $this->assertTrue($metadata->isCopiedToChildResource());
    }

    public function testUpdatingDisplayStrategy() {
        $metadata = Metadata::create(
            'books',
            MetadataControl::TEXT(),
            'Prop',
            ['PL' => 'AA'],
            ['PL' => 'AA'],
            ['PL' => 'AA'],
            [],
            'oldGroup',
            'aa'
        );
        $metadata->update(['PL' => 'AA'], [], [], [], 'newGroup', 'bb', true, true);
        $this->assertEquals('bb', $metadata->getDisplayStrategy());
        $metadata->update(['PL' => 'AA'], [], [], [], 'newGroup', '   bb   ', true, true);
        $this->assertEquals('bb', $metadata->getDisplayStrategy());
        $this->expectException(\InvalidArgumentException::class);
        $metadata->update(['PL' => 'AA'], [], [], [], 'newGroup', null, true, true);
    }

    public function testUpdatingDisplayStrategyOfNonDynamicMetadata() {
        $metadata = Metadata::create(
            'books',
            MetadataControl::TEXT(),
            'Prop',
            ['PL' => 'AA'],
            ['PL' => 'AA'],
            ['PL' => 'AA'],
            [],
            'oldGroup',
            null
        );
        $this->expectException(\InvalidArgumentException::class);
        $metadata->update(['PL' => 'AA'], [], [], [], 'newGroup', 'bb', true, true);
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
        $metadata->update(['PL' => 'CC'], [], [], [], '', null, true, true);
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

    public function testOverridingGroupId() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], 'oldGroup');
        $metadata = $metadata->withOverrides(['groupId' => 'overriddenGroup']);
        $this->assertEquals('overriddenGroup', $metadata->getGroupId());
    }

    public function testChangedGroupIsLessImportantThanOverride() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], 'oldGroup');
        $metadata = $metadata->withOverrides(['groupId' => 'overridenGroup']);
        $metadata->update(['PL' => 'CC'], [], [], [], 'updatedGroup', null, true, true);
        $this->assertEquals('overridenGroup', $metadata->getGroupId());
    }

    public function testGroupIdCanBeOverridenWithEmptyString() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], 'oldGroup');
        $metadata = $metadata->withOverrides(['groupId' => '']);
        $this->assertEquals(null, $metadata->getGroupId());
    }

    public function testOverridingGroupIdWithNullIsIgnored() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], 'group');
        $metadata = $metadata->withOverrides(['groupId' => null]);
        $this->assertEquals('group', $metadata->getGroupId());
    }

    public function testGroupIdOverrideWithSameValueAsExistingIsRemoved() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], 'oldGroup');
        $metadata = $metadata->withOverrides(['groupId' => 'oldGroup']);
        $metadata->update(['PL' => 'CC'], [], [], [], 'updatedGroup', null, true, true);
        $this->assertEquals('updatedGroup', $metadata->getGroupId());
    }

    public function testOverridingShownInBrief() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], '', null, true, true);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides(['shownInBrief' => false]);
        $this->assertFalse($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides(['shownInBrief' => true]);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata->update([], [], [], [], '', null, false, false, null);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides(['shownInBrief' => null]);
        $this->assertFalse($metadata->isShownInBrief());
        $metadata->update([], [], [], [], '', null, true, false, null);
        $this->assertTrue($metadata->isShownInBrief());
        $metadata = $metadata->withOverrides([]);
        $this->assertTrue($metadata->isShownInBrief());
    }

    public function testOverriddenShownInBriefIsNotPresentInOverrides() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], '', null, true, true);
        $metadata = $metadata->withOverrides(['shownInBrief' => true]);
        $this->assertArrayNotHasKey('shownInBrief', $metadata->getOverrides());
    }

    public function testOverridingCopyToChildResource() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], '', null, true, true);
        $this->assertTrue($metadata->isCopiedToChildResource());
        $metadata = $metadata->withOverrides(['copyToChildResource' => false]);
        $this->assertFalse($metadata->isCopiedToChildResource());
        $metadata = $metadata->withOverrides(['copyToChildResource' => true]);
        $this->assertTrue($metadata->isCopiedToChildResource());
        $metadata->update([], [], [], [], '', null, false, false);
        $this->assertTrue($metadata->isCopiedToChildResource());
        $metadata = $metadata->withOverrides(['copyToChildResource' => null]);
        $this->assertFalse($metadata->isCopiedToChildResource());
        $metadata->update([], [], [], [], '', null, true, true);
        $this->assertTrue($metadata->isCopiedToChildResource());
        $metadata = $metadata->withOverrides([]);
        $this->assertTrue($metadata->isCopiedToChildResource());
    }

    public function testOverridingDisplayStrategyForNonDynamicMetadata() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], '', null, true, true);
        $this->assertFalse($metadata->isDynamic());
        $this->assertNull($metadata->getDisplayStrategy());
        $metadata = $metadata->withOverrides(['displayStrategy' => null]);
        $this->assertFalse($metadata->isDynamic());
        $metadata = $metadata->withOverrides(['displayStrategy' => '']);
        $this->assertFalse($metadata->isDynamic());
        $this->expectException(\InvalidArgumentException::class);
        $metadata->withOverrides(['displayStrategy' => 'something']);
    }

    public function testOverridingDisplayStrategyForDynamicMetadata() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], '', 'dynamic', true, true);
        $this->assertTrue($metadata->isDynamic());
        $this->assertEquals('dynamic', $metadata->getDisplayStrategy());
        $metadata = $metadata->withOverrides(['displayStrategy' => 'other']);
        $this->assertEquals('other', $metadata->getDisplayStrategy());
        $metadata = $metadata->withOverrides(['displayStrategy' => null]);
        $this->assertTrue($metadata->isDynamic());
        $this->assertEquals('dynamic', $metadata->getDisplayStrategy());
        $metadata = $metadata->withOverrides(['displayStrategy' => '']);
        $this->assertTrue($metadata->isDynamic());
        $this->assertEquals('dynamic', $metadata->getDisplayStrategy());
        $metadata = $metadata->withOverrides(['displayStrategy' => '  ']);
        $this->assertTrue($metadata->isDynamic());
        $this->assertEquals('dynamic', $metadata->getDisplayStrategy());
        $metadata = $metadata->withOverrides(['displayStrategy' => 'dynamic']);
        $this->assertEmpty($metadata->getOverrides());
        $metadata = $metadata->withOverrides(['displayStrategy' => '   dynamic   ']);
        $this->assertEmpty($metadata->getOverrides());
    }

    public function testOverriddenCopyToChildResourceIsNotPresentInOverrides() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], [], '', null, true, true);
        $metadata = $metadata->withOverrides(['copyToChildResource' => true]);
        $this->assertArrayNotHasKey('copyToChildResource', $metadata->getOverrides());
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

    public function testOverridingMaxCountConstraintWithNull() {
        $metadata = Metadata::create('books', MetadataControl::TEXT(), 'Prop', [], [], [], ['maxCount' => 1]);
        $this->assertEquals(['maxCount' => 1], $metadata->getConstraints());
        $metadata = $metadata->withOverrides(['constraints' => ['maxCount' => null]]);
        $this->assertEquals(['maxCount' => null], $metadata->getConstraints());
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

    public function testOverridingIntegerConstraints() {
        $minMaxValue = ['minMaxValue' => ['min' => 1000, 'max' => 2000]];
        $metadata = Metadata::create('books', MetadataControl::INTEGER(), 'Prop', [], [], [], $minMaxValue);
        $this->assertEquals(['minMaxValue' => ['min' => 1000, 'max' => 2000]], $metadata->getConstraints());
        $metadata = $metadata->withOverrides(['constraints' => ['minMaxValue' => ['max' => 3000]]]);
        $this->assertEquals(['minMaxValue' => ['max' => 3000]], $metadata->getConstraints());
    }

    public function testAddingAndClearingIntegerConstraintWithOverride() {
        $minMaxValue = ['minMaxValue' => ['min' => 1000, 'max' => 2000]];
        $metadata = Metadata::create('books', MetadataControl::INTEGER(), 'Prop', [], [], [], $minMaxValue);
        $metadata = $metadata->withOverrides(['constraints' => ['count' => 2]]);
        $this->assertEquals(['minMaxValue' => ['min' => 1000, 'max' => 2000], 'count' => 2], $metadata->getConstraints());
        $minMaxValue = ['minMaxValue' => ['min' => 1000, 'max' => 2000]];
        $metadata = Metadata::create('books', MetadataControl::INTEGER(), 'Prop', [], [], [], $minMaxValue);
        $metadata = $metadata->withOverrides(['constraints' => ['minMaxValue' => null]]);
        $this->assertEquals(['minMaxValue' => null], $metadata->getConstraints());
    }

    public function testCanDetermineAssignees() {
        $resourceKindRepository = $this->createRepositoryStub(
            ResourceKindRepository::class,
            [
                $this->createResourceKindMock(1, SystemResourceClass::USER),
                $this->createResourceKindMock(2, SystemResourceClass::USER),
                $this->createResourceKindMock(3),
            ]
        );
        $metadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'skaner', []);
        $this->assertFalse($metadata->canDetermineAssignees($resourceKindRepository));
        $metadata = $metadata->withOverrides(['constraints' => ['resourceKind' => [1]]]);
        $this->assertTrue($metadata->canDetermineAssignees($resourceKindRepository));
        $metadata = $metadata->withOverrides(['constraints' => ['resourceKind' => [1, 2]]]);
        $this->assertTrue($metadata->canDetermineAssignees($resourceKindRepository));
        $metadata = $metadata->withOverrides(['constraints' => ['resourceKind' => [1, 2, 3]]]);
        $this->assertFalse($metadata->canDetermineAssignees($resourceKindRepository));
        $metadata = $metadata->withOverrides(['constraints' => ['resourceKind' => [3]]]);
        $this->assertFalse($metadata->canDetermineAssignees($resourceKindRepository));
    }
}
