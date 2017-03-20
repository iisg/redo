<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommand */
    private $metadataCreateCommand;
    /** @var MetadataFactory */
    private $factory;
    private $newChildMetadata;

    protected function setUp() {
        $this->metadataCreateCommand = new MetadataCreateCommand('nazwa', ['PL' => 'Labelka'], [], [], 'textarea');
        $this->factory = new MetadataFactory();
        $this->newChildMetadata = [
            'name' => 'nazwa',
            'label' => ['PL' => 'Test'],
            'placeholder' => [],
            'description' => [],
            'control' => 'textarea'
        ];
    }

    public function testCreatingMetadata() {
        $metadata = $this->factory->create($this->metadataCreateCommand);
        $this->assertEquals('nazwa', $metadata->getName());
        $this->assertEquals('Labelka', $metadata->getLabel()['PL']);
        $this->assertEmpty($metadata->getPlaceholder());
        $this->assertEmpty($metadata->getDescription());
        $this->assertEquals('textarea', $metadata->getControl());
    }

    public function testCreatingChildMetadata() {
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

    public function testCreatingForResourceKind() {
        $metadata = $this->factory->create($this->metadataCreateCommand);
        $base = $this->factory->create($this->metadataCreateCommand);
        $base->update([], ['PL' => 'base'], []);
        $resourceKind = new ResourceKind(['PL' => 'rodzaj']);
        $created = $this->factory->createForResourceKind($resourceKind, $base, $metadata);
        $this->assertSame($resourceKind, $created->getResourceKind());
        $this->assertEquals('base', $created->getPlaceholder()['PL']);
    }
}
