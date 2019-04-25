<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Metadata\MetadataValueAdjuster\MetadataValueAdjusterComposite;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\Repository\ResourceKindRepository;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;
use Repeka\Domain\UseCase\Resource\ResourceListQueryAdjuster;
use Repeka\Domain\UseCase\Resource\ResourceListQuerySort;
use Repeka\Tests\Traits\StubsTrait;

class ResourceListQueryAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var  ResourceListQueryAdjuster */
    private $adjuster;

    protected function setUp() {
        $this->adjuster = new ResourceListQueryAdjuster(
            $this->createRepositoryStub(MetadataRepository::class),
            $this->createRepositoryStub(ResourceKindRepository::class),
            $this->createMock(MetadataValueAdjusterComposite::class)
        );
    }

    public function testPrepareSortByArray() {
        $expectedSort = [
            ['columnId' => 'id', 'direction' => 'DESC'],
            ['columnId' => 123, 'direction' => 'ASC'],
        ];
        $command = ResourceListQuery::builder()
            ->sortBy([ResourceListQuerySort::idDesc(), ResourceListQuerySort::asc(123)])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($command);
        $this->assertEquals($expectedSort, $adjusted->getSortBy());
    }
}
