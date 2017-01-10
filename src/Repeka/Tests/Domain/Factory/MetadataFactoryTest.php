<?php
namespace Repeka\Tests\Domain\Factory;

use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\UseCase\Metadata\MetadataCreateCommand;

class MetadataFactoryTest extends \PHPUnit_Framework_TestCase {
    /** @var MetadataCreateCommand */
    private $metadataCreateCommand;
    /** @var MetadataFactory */
    private $factory;

    protected function setUp() {
        $this->metadataCreateCommand = new MetadataCreateCommand('nazwa', ['PL' => 'Labelka'], [], [], 'textarea');
        $this->factory = new MetadataFactory();
    }

    public function testCreatingMetadata() {
        $metadata = $this->factory->create($this->metadataCreateCommand);
        $this->assertEquals('nazwa', $metadata->getName());
        $this->assertEquals('Labelka', $metadata->getLabel()['PL']);
        $this->assertEmpty($metadata->getPlaceholder());
        $this->assertEmpty($metadata->getDescription());
        $this->assertEquals('textarea', $metadata->getControl());
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
