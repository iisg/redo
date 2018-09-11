<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\ColumnSortDataConverter;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceListQueryAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceListQueryAdjuster */
    private $adjuster;
    private $resourceKind;
    private $resourceKind2;
    private $columnSortDataConverter;

    protected function setUp() {
        $realMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'name', ['PL' => 'A']);
        EntityUtils::forceSetId($realMetadata, 11);
        $realResourceKind = new ResourceKind([], [$realMetadata]);
        EntityUtils::forceSetId($realResourceKind, 11);
        $this->resourceKind = $this->createResourceKindMock(1, 'book', [$realMetadata]);
        $this->resourceKind2 = $this->createResourceKindMock(2, 'book', [$realMetadata]);
        $this->columnSortDataConverter = $this->createMock(ColumnSortDataConverter::class);
        $this->columnSortDataConverter->expects($this->once())->method('convertSortByMetadataColumnsToIntegers');
        $resourceKindRepository = $this->createRepositoryStub(
            ResourceKindRepository::class,
            [
                $realResourceKind,
                $this->resourceKind,
                $this->resourceKind2,
            ]
        );
        $this->adjuster = new ResourceListQueryAdjuster(
            $this->createRepositoryStub(MetadataRepository::class),
            $resourceKindRepository,
            $this->columnSortDataConverter
        );
    }

    public function testConvertResourceKindIdsToResourceKinds() {
        $command = ResourceListQuery::builder()->filterByResourceKinds([1, 2])->build();
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals([$this->resourceKind, $this->resourceKind2], $command->getResourceKinds());
    }
}
