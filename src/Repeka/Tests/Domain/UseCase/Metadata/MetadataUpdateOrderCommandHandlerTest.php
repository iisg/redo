<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommand;
use Repeka\Domain\UseCase\Metadata\MetadataUpdateOrderCommandHandler;

class MetadataUpdateOrderCommandHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;

    /** @var MetadataUpdateOrderCommandHandler */
    private $commandHandler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->commandHandler = new MetadataUpdateOrderCommandHandler($this->metadataRepository);
    }

    public function testUpdatingOrderOfOne() {
        $metadata = $this->createMock(Metadata::class);
        $this->metadataRepository->expects($this->once())->method('findAllBaseByResourceClass')->willReturn([$metadata]);
        $this->metadataRepository->expects($this->once())->method('save')->with($metadata)->willReturnArgument(0);
        $metadata->expects($this->once())->method('updateOrdinalNumber')->with(0);
        $metadata->expects($this->any())->method('getId')->willReturn(1);
        $this->commandHandler->handle(new MetadataUpdateOrderCommand([1], 'books'));
    }

    public function testUpdatingOrderOfThree() {
        $metadata1 = $this->createMock(Metadata::class);
        $metadata2 = $this->createMock(Metadata::class);
        $metadata3 = $this->createMock(Metadata::class);
        $this->metadataRepository
            ->expects($this->once())
            ->method('findAllBaseByResourceClass')
            ->willReturn([$metadata1, $metadata2, $metadata3]);
        $this->metadataRepository->expects($this->any())->method('save')->willReturnArgument(0);
        $metadata1->expects($this->any())->method('getId')->willReturn(1);
        $metadata2->expects($this->any())->method('getId')->willReturn(2);
        $metadata3->expects($this->any())->method('getId')->willReturn(3);
        $metadata1->expects($this->once())->method('updateOrdinalNumber')->with(1);
        $metadata2->expects($this->once())->method('updateOrdinalNumber')->with(0);
        $metadata3->expects($this->once())->method('updateOrdinalNumber')->with(2);
        $this->commandHandler->handle(new MetadataUpdateOrderCommand([2, 1, 3], 'books'));
    }

    public function testUpdatingNonExistingMetadata() {
        $this->expectException(InvalidArgumentException::class);
        $metadata = $this->createMock(Metadata::class);
        $this->metadataRepository->expects($this->once())->method('findAllBaseByResourceClass')->willReturn([$metadata]);
        $metadata->expects($this->any())->method('getId')->willReturn(1);
        $this->commandHandler->handle(new MetadataUpdateOrderCommand([2], 'books'));
    }
}
