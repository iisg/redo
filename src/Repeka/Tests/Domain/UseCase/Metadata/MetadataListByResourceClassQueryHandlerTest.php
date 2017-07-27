<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListByResourceClassQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListByResourceClassQueryHandler;

class MetadataListByResourceClassQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataListByResourceClassQueryHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataListByResourceClassQueryHandler($this->metadataRepository);
    }

    public function testGettingTheList() {
        $metadataList = [$this->createMock(Metadata::class)];
        $this->metadataRepository->expects($this->once())->method('findAllBaseByResourceClass')->willReturn($metadataList);
        $returnedList = $this->handler->handle(new MetadataListByResourceClassQuery('books'));
        $this->assertSame($metadataList, $returnedList);
    }
}
