<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Assert\Assertion;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Repeka\Domain\Entity\Metadata;
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
    /** @var MetadataChildWithBaseCreateCommandHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->parent = $this->createMock(Metadata::class);
        $this->parent->expects($this->atLeastOnce())->method('getId')->willReturn(1);
        $this->base = $this->createMock(Metadata::class);
        $this->base->expects($this->atLeastOnce())->method('getId')->willReturn(2);
        $this->metadataChildWithBaseCreateCommand = new MetadataChildWithBaseCreateCommand($this->parent, $this->base);
        $this->handler = new MetadataChildWithBaseCreateCommandHandler($this->metadataRepository);
    }

    public function testCreatingChildMetadataWithBaseAndParent() {
        $this->metadataRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $metadataChild = $this->handler->handle($this->metadataChildWithBaseCreateCommand);
        $this->assertSame($this->parent->getId(), $metadataChild->getParentId());
        $this->assertSame($this->base->getId(), $metadataChild->getBaseId());
    }
}
