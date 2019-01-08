<?php
namespace Repeka\Domain\Metadata\MetadataImport\Transform;

use Assert\InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Tests\Traits\StubsTrait;

class ContentSearchImportTransformTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ContentSearchImportTransform */
    private $contentSearchTransform;

    /** @var ResourceRepository | PHPUnit_Framework_MockObject_MockObject */
    private $resourceRepository;

    /** @var MetadataRepository | PHPUnit_Framework_MockObject_MockObject */
    private $metadataRepository;

    /** @var ResourceEntity | PHPUnit_Framework_MockObject_MockObject */
    private $resource1;

    /** @var ResourceEntity | PHPUnit_Framework_MockObject_MockObject */
    private $resource2;

    public function testSearchExactValue() {

        $this->resourceRepository->expects($this->once())->method('findByQuery')->willReturn(new PageResult([$this->resource1], 1, 1));
        $this->contentSearchTransform = new ContentSearchImportTransform($this->metadataRepository, $this->resourceRepository);
        $this->assertEquals(
            ['1'],
            $this->contentSearchTransform->apply(['ala'], ['metadata' => 1, 'exact' => 'true'], [])
        );
    }

    public function testSearchValue() {
        $this->resourceRepository->expects($this->once())->method('findByQuery')->willReturn(
            new PageResult([$this->resource1, $this->resource2], 2, 1)
        );
        $this->contentSearchTransform = new ContentSearchImportTransform($this->metadataRepository, $this->resourceRepository);
        $this->assertEquals(
            ['1', '2'],
            $this->contentSearchTransform->apply(['ala'], ['metadata' => 1, 'exact' => 'false'], [])
        );
    }

    public function testNoGivenMetadata() {
        $this->contentSearchTransform = new ContentSearchImportTransform($this->metadataRepository, $this->resourceRepository);
        $this->expectException(InvalidArgumentException::class);
        $this->contentSearchTransform->apply(['ala'], ['exact' => 'false'], []);
    }

    protected function setUp() {

        $metadata = $this->createMetadataMock(1);
        $this->metadataRepository = $this->createMetadataRepositoryStub([$metadata]);
        $resourceKind = $this->createResourceKindMock(1, 'books', [$metadata]);
        $this->resource1 = $this->createResourceMock(1, $resourceKind, [1 => 'ala']);
        $this->resource2 = $this->createResourceMock(2, $resourceKind, [1 => 'ala ma kota']);
        $this->resourceRepository = $this->createRepositoryStub(ResourceRepository::class, [$this->resource1, $this->resource2]);
    }
}
