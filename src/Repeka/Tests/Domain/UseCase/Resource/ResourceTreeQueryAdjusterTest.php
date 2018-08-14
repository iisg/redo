<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceTreeQuery;
use Repeka\Domain\UseCase\Resource\ResourceTreeQueryAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceTreeQueryAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceTreeQueryAdjuster */
    private $adjuster;
    private $resourceKind;
    private $resourceKind2;

    protected function setUp() {
        $realMetadata = Metadata::create('books', MetadataControl::RELATIONSHIP(), 'name', ['PL' => 'A']);
        EntityUtils::forceSetId($realMetadata, 11);
        $realResourceKind = new ResourceKind([], [$realMetadata]);
        EntityUtils::forceSetId($realResourceKind, 11);
        $this->resourceKind = $this->createResourceKindMock(1, 'book', [$realMetadata]);
        $this->resourceKind2 = $this->createResourceKindMock(2, 'book', [$realMetadata]);
        /** @var ResourceKindRepository|\PHPUnit_Framework_MockObject_MockObject $resourceKindRepository */
        $resourceKindRepository = $this->createRepositoryStub(
            ResourceKindRepository::class,
            [
                $realResourceKind,
                $this->resourceKind,
                $this->resourceKind2,
            ]
        );
        $metadata = $this->createMetadataMock(1, null, null, [], '', [], 'firstMetadata');
        $this->adjuster = new ResourceTreeQueryAdjuster(
            $this->createMetadataRepositoryStub([$metadata]),
            $resourceKindRepository
        );
    }

    public function testConvertResourceKindIdsToResourceKinds() {
        $command = ResourceTreeQuery::builder()->filterByResourceKinds([1, 2])->build();
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals([$this->resourceKind, $this->resourceKind2], $command->getResourceKinds());
    }

    public function testMapsMetadataNamesToIds() {
        $command = ResourceTreeQuery::builder()->filterByContents(['firstMetadata' => 'filter'])->build();
        $command = $this->adjuster->adjustCommand($command);
        $this->assertEquals(ResourceContents::fromArray([1 => 'filter']), $command->getContentsFilter());
    }
}
