<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class MetadataListQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataListQueryHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataListQueryHandler($this->metadataRepository);
    }

    public function testGettingTheList() {
        $metadataList = [$this->createMock(Metadata::class)];
        $this->metadataRepository->expects($this->once())->method('findAllBase')->willReturn($metadataList);
        $returnedList = $this->handler->handle(new MetadataListQuery());
        $this->assertSame($metadataList, $returnedList);
    }

    public function testGettingTheChildrenList() {
        $metadataList = [$this->createMock(Metadata::class)];
        $this->metadataRepository->expects($this->once())->method('findAllChildren')->willReturn($metadataList);
        $returnedList = $this->handler->handle(new MetadataListQuery(2));
        $this->assertSame($metadataList, $returnedList);
    }
}
