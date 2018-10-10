<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQueryAdjuster;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Traits\StubsTrait;

class ResourceListFtsQueryAdjusterTest extends \PHPUnit_Framework_TestCase {
    use StubsTrait;

    /** @var ResourceListFtsQueryAdjuster */
    private $adjuster;

    protected function setUp() {
        $metadataRepository = $this->createMock(MetadataRepository::class);
        $knownMetadata = ['tytul' => $this->createMetadataMock(1), 'opis' => $this->createMetadataMock(2)];
        $metadataRepository->method('findByNameOrId')->willReturnCallback(
            function ($nameOrId) use ($knownMetadata) {
                if (isset($knownMetadata[$nameOrId])) {
                    return $knownMetadata[$nameOrId];
                }
                $lookup = EntityUtils::getLookupMap($knownMetadata);
                if (isset($lookup[$nameOrId])) {
                    return $lookup[$nameOrId];
                }
                throw new EntityNotFoundException('metadata', $nameOrId);
            }
        );
        $this->adjuster = new ResourceListFtsQueryAdjuster($metadataRepository);
    }

    public function testConvertsSearchableMetadataNamesToIds() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('Unicorn')
            ->setSearchableMetadata(['tytul', 'opis'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1, 2], EntityUtils::mapToIds($adjusted->getSearchableMetadata()));
    }

    public function testConvertsSearchableMetadataNamesToIdsWhenMixedWithIds() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('Unicorn')
            ->setSearchableMetadata(['tytul', 2])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1, 2], EntityUtils::mapToIds($adjusted->getSearchableMetadata()));
    }

    public function testFailsWhenSearchableMetadataNotFound() {
        $this->expectException(EntityNotFoundException::class);
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('Unicorn')
            ->setSearchableMetadata(['tytul', 'unicorn'])
            ->build();
        $this->adjuster->adjustCommand($query);
    }

    public function testConvertsFacetedMetadata() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('Unicorn')
            ->setMetadataFacets(['tytul', 'opis'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1, 2], EntityUtils::mapToIds($adjusted->getFacetedMetadata()));
    }

    public function testConvertsFacetFilters() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('Unicorn')
            ->setFacetsFilters(['tytul' => 'aaa'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1 => 'aaa'], $adjusted->getFacetsFilters());
    }

    public function testLeavesKindIdFacetFilters() {
        $query = ResourceListFtsQuery::builder()
            ->setPhrase('Unicorn')
            ->setFacetsFilters(['tytul' => 'aaa', 'kindId' => 'bbb'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1 => 'aaa', 'kindId' => 'bbb'], $adjusted->getFacetsFilters());
    }
}
