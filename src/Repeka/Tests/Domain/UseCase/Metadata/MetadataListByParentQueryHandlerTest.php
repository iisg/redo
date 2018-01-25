<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListByParentQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListByParentQueryHandler;
use Repeka\Tests\Traits\StubsTrait;

class MetadataListByParentQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataListByParentQueryHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataListByParentQueryHandler($this->metadataRepository);
    }

    public function testGettingTheChildrenList() {
        $metadataList = [$this->createMock(Metadata::class)];
        $this->metadataRepository->expects($this->once())->method('findByParent')->willReturn($metadataList);
        $returnedList = $this->handler->handle(new MetadataListByParentQuery($this->createMetadataMock()));
        $this->assertSame($metadataList, $returnedList);
    }
}
