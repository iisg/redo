<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Assert\Assertion;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Factory\MetadataFactory;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommand;
use Repeka\Domain\UseCase\Metadata\MetadataChildCreateCommandHandler;

class MetadataChildCreateCommandHandlerTest extends PHPUnit_Framework_TestCase {
    /** @var Metadata */
    private $base;
    /** @var Metadata */
    private $parent;
    /** @var MetadataChildCreateCommand */
    private $metadataChildCreateCommand;
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataChildCreateCommandHandler */
    private $handler;


    protected function setUp() {
        $this->base = $this->createMock(Metadata::class);
        $this->base->expects($this->atLeastOnce())->method('getId')->willReturn(3);
        $this->parent = $this->createMock(Metadata::class);
        $this->parent->expects($this->atLeastOnce())->method('getId')->willReturn(4);
        $this->metadataChildCreateCommand = new MetadataChildCreateCommand($this->base, $this->parent);
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataChildCreateCommandHandler($this->metadataRepository);
    }

    public function testCreatingChildMetadata() {
        $this->metadataRepository->expects($this->once())->method('save')->willReturnArgument(0);
        $metadataChild = $this->handler->handle($this->metadataChildCreateCommand);
        $this->assertSame($this->parent->getId(), $metadataChild->getParentId());
        $this->assertSame($this->base->getId(), $metadataChild->getBaseId());
    }
}
