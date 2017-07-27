<?php
namespace Repeka\Tests\Domain\UseCase\Metadata;

use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Metadata\MetadataListByParentIdQuery;
use Repeka\Domain\UseCase\Metadata\MetadataListByParentIdQueryHandler;

class MetadataListByParentIdQueryHandlerTest extends \PHPUnit_Framework_TestCase {
    /** @var PHPUnit_Framework_MockObject_MockObject|MetadataRepository */
    private $metadataRepository;
    /** @var MetadataListByParentIdQueryHandler */
    private $handler;

    protected function setUp() {
        $this->metadataRepository = $this->createMock(MetadataRepository::class);
        $this->handler = new MetadataListByParentIdQueryHandler($this->metadataRepository);
    }

    public function testGettingTheChildrenList() {
        $metadataList = [$this->createMock(Metadata::class)];
        $this->metadataRepository->expects($this->once())->method('findAllChildren')->willReturn($metadataList);
        $returnedList = $this->handler->handle(new MetadataListByParentIdQuery(2));
        $this->assertSame($metadataList, $returnedList);
    }
}
