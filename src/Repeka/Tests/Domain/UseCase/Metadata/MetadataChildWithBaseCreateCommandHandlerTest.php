<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataChildWithBaseCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildWithBaseCreateCommandHandler;

class MetadataChildWithBaseCreateCommandHandlerTest extends PHPUnit_Framework_TestCase {
    /** @var Metadata */
    private $base;
    /** @var Metadata */
    private $parent;
    /** @var MetadataChildWithBaseCreateCommand */
    private $metadataChildWithBaseCreateCommand;
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataFactory */
    private $metadataFactory;
    /** @var MetadataChildWithBaseCreateCommandHandler */
    private $handler;
    private $newChildMetadata;

    protected function setUp() {
        $this->newChildMetadata = [
            'label' => ['PL' => 'Label'],
            'placeholder' => ['PL' => 'Placeholder'],
            'description' => ['PL' => 'Description'],
            'constraints' => [],
            'shownInBrief' => false,
        ];
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->metadataFactory = new MetadataFactory();
        $this->parent = $this->createMock(Metadata::class);
        $this->parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->base = $this->createMock(Metadata::class);
        $this->base->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $this->metadataChildWithBaseCreateCommand = new MetadataChildWithBaseCreateCommand(
            $this->parent,
            $this->base,
            $this->newChildMetadata
        );
        $this->handler = new MetadataChildWithBaseCreateCommandHandler($this->metadataRepository, $this->metadataFactory);
    }

    public function testCreatingChildMetadataWithBaseAndParent() {
        $this->metadataRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $metadataChild = $this->handler->handle($this->metadataChildWithBaseCreateCommand);
        $this->assertSame($this->parent->getId(), $metadataChild->getParentId());
        $this->assertSame($this->base->getId(), $metadataChild->getBaseId());
        $this->assertEquals('Label', $metadataChild->getLabel()['PL']);
        $this->assertEquals('Placeholder', $metadataChild->getPlaceholder()['PL']);
        $this->assertEquals('Description', $metadataChild->getDescription()['PL']);
        $this->assertEquals([], $metadataChild->getConstraints());
    }
}
