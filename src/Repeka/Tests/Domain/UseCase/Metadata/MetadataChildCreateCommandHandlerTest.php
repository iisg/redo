<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommandHandler;

class MetadataChildCreateCommandHandlerTest extends PHPUnit_Framework_TestCase {
    /** @var Metadata */
    private $parent;
    /** @var MetadataChildCreateCommand */
    private $metadataChildCreateCommand;
    /** @var MetadataFactory */
    private $metadataFactory;
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataChildCreateCommandHandler */
    private $handler;
    private $newChildMetadata;

    protected function setUp() {
        $this->newChildMetadata = [
            'name' => 'nazwa',
            'label' => ['PL' => 'Test'],
            'placeholder' => [],
            'description' => [],
            'control' => 'textarea',
            'shownInBrief' => false,
        ];
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->parent = $this->createMock(Metadata::class);
        $this->parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->metadataChildCreateCommand = new MetadataChildCreateCommand($this->parent, $this->newChildMetadata);
        $this->metadataFactory = new MetadataFactory();
        $this->handler = new MetadataChildCreateCommandHandler($this->metadataFactory, $this->metadataRepository);
    }

    public function testCreatingNewChildMetadata() {
        $this->metadataRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $metadataChild = $this->handler->handle($this->metadataChildCreateCommand);
        $this->assertEquals(1, $metadataChild->getParentId());
        $this->assertEquals('nazwa', $metadataChild->getName());
        $this->assertEquals('Test', $metadataChild->getLabel()['PL']);
        $this->assertEmpty($metadataChild->getPlaceholder());
        $this->assertEmpty($metadataChild->getDescription());
        $this->assertEquals(MetadataControl::TEXTAREA(), $metadataChild->getControl());
    }
}
