<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommand */
    private $textareaMetadataCreateCmd;
    /** @var MetadataCreateCommand */
    private $relationshipMetadataCreateCmd;
    /** @var MetadataFactory */
    private $factory;
    private $newChildMetadata;

    protected function setUp() {
        $this->textareaMetadataCreateCmd = new MetadataCreateCommand('nazwa', ['PL' => 'Labelka'], [], [], 'textarea', [], false);
        $this->relationshipMetadataCreateCmd = new MetadataCreateCommand('nazwa', ['PL' => 'Labelka'], [], [], 'relationship', [], false);
        $this->factory = new MetadataFactory();
        $this->newChildMetadata = [
            'name' => 'nazwa',
            'label' => ['PL' => 'Test'],
            'placeholder' => [],
            'description' => [],
            'control' => 'textarea',
            'constraints' => [],
            'shownInBrief' => false,
        ];
    }

    public function testCreatingMetadata() {
        $metadata = $this->factory->create($this->textareaMetadataCreateCmd);
        $this->assertEquals('nazwa', $metadata->getName());
        $this->assertEquals('Labelka', $metadata->getLabel()['PL']);
        $this->assertEmpty($metadata->getPlaceholder());
        $this->assertEmpty($metadata->getDescription());
        $this->assertEquals('textarea', $metadata->getControl());
    }

    public function testCreatingChildMetadataWithParent() {
        $parent = $this->createMock(Metadata::class);
        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $created = $this->factory->createWithParent($this->newChildMetadata, $parent);
        $this->assertEquals(1, $created->getParentId());
        $this->assertEquals('nazwa', $created->getName());
        $this->assertEquals('Test', $created->getLabel()['PL']);
        $this->assertEmpty($created->getPlaceholder());
        $this->assertEmpty($created->getDescription());
        $this->assertEquals('textarea', $created->getControl());
    }

    public function testCreatingChildMetadataWithBaseAndParent() {
        $parent = $this->createMock(Metadata::class);
        $parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $base = $this->createMock(Metadata::class);
        $base->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $created = $this->factory->createWithBaseAndParent($base, $parent, $this->newChildMetadata);
        $this->assertEquals(1, $created->getParentId());
        $this->assertEquals(2, $created->getBaseId());
        $this->assertEquals('Test', $created->getLabel()['PL']);
        $this->assertEmpty($created->getPlaceholder());
        $this->assertEmpty($created->getDescription());
        $this->assertEquals([], $created->getConstraints());
    }

    public function testCreatingForResourceKind() {
        $metadata = $this->factory->create($this->textareaMetadataCreateCmd);
        $base = $this->factory->create($this->textareaMetadataCreateCmd);
        $base->update([], ['PL' => 'base'], [], [], false);
        $resourceKind = new ResourceKind(['PL' => 'rodzaj']);
        $created = $this->factory->createForResourceKind($resourceKind, $base, $metadata);
        $this->assertSame($resourceKind, $created->getResourceKind());
        $this->assertEquals('base', $created->getPlaceholder()['PL']);
    }

    public function testRemovingUnmodifiedConstraints() {
        $originalConstraints = ['resourceKind' => [0]];
        $filteredConstraints = $this->factory->removeUnmodifiedConstraints($originalConstraints, $originalConstraints);
        $this->assertEmpty($filteredConstraints);
    }

    public function testKeepingModifiedConstraints() {
        $originalConstraints = ['resourceKind' => [0]];
        $modifiedConstraints = ['resourceKind' => [1]];
        $filteredConstraints = $this->factory->removeUnmodifiedConstraints($modifiedConstraints, $originalConstraints);
        $this->assertCount(1, $filteredConstraints);
        $this->assertEquals($modifiedConstraints, $filteredConstraints);
    }

    public function testOverridingWithNoConstraints() {
        $originalConstraints = ['resourceKind' => [0]];
        $modifiedConstraints = ['resourceKind' => []];
        $filteredConstraints = $this->factory->removeUnmodifiedConstraints($modifiedConstraints, $originalConstraints);
        $this->assertCount(1, $filteredConstraints);
        $this->assertEquals($modifiedConstraints, $filteredConstraints);
    }
}
