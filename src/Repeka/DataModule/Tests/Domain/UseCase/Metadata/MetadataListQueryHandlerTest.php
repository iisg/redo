<?php
namespace Repeka\DataModule\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\DataModule\Domain\Entity\Metadata;
use Repeka\DataModule\Domain\Repository\MetadataRepository;
use Repeka\DataModule\Domain\UseCase\Metadata\MetadataListQueryHandler;

class MetadataListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;
    /** @var MetadataListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataListQueryHandler($this->metadataRepository);
    }

    public function testGettingTheList() {
        $metadataList = [$this->createMock(Metadata::class)];
        $this->metadataRepository->expects($this->once())->method('findAll')->willReturn($metadataList);
        $returnedList = $this->handler->handle();
        $this->assertSame($metadataList, $returnedList);
    }
}
