<?php
namespace Repeka\Tests\Domain\UseCase\Resource;

use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Metadata\SearchValueAdjuster\SearchValueAdjusterComposite;
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
        $searchValueAdjusterComposite = $this->createMock(SearchValueAdjusterComposite::class);
        $searchValueAdjusterComposite->method('adjustSearchValue')->withAnyParameters()->willReturn('aaa');
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
        $this->adjuster = new ResourceListFtsQueryAdjuster($metadataRepository, $searchValueAdjusterComposite);
    }

    public function testConvertsSearchableMetadataNamesToIds() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setSearchableMetadata(['tytul', 'opis'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1, 2], EntityUtils::mapToIds($adjusted->getSearchableMetadata()));
    }

    public function testConvertsSearchableMetadataNamesToIdsWhenMixedWithIds() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setSearchableMetadata(['tytul', 2])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1, 2], EntityUtils::mapToIds($adjusted->getSearchableMetadata()));
    }

    public function testFailsWhenSearchableMetadataNotFound() {
        $this->expectException(EntityNotFoundException::class);
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setSearchableMetadata(['tytul', 'unicorn'])
            ->build();
        $this->adjuster->adjustCommand($query);
    }

    public function testConvertsFacetedMetadata() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setMetadataFacets(['tytul', 'opis'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertEquals([1, 2], EntityUtils::mapToIds($adjusted->getFacetedMetadata()));
    }

    public function testConvertsFacetFilters() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setFacetsFilters(['tytul' => 'aaa'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertCount(1, $adjusted->getFacetsFilters());
        $this->assertEquals(1, $adjusted->getFacetsFilters()[0][0]->getId());
        $this->assertEquals('aaa', $adjusted->getFacetsFilters()[0][1]);
    }

    public function testLeavesKindIdFacetFilters() {
        $query = ResourceListFtsQuery::builder()
            ->addPhrase('Unicorn')
            ->setFacetsFilters(['tytul' => 'aaa', 'kindId' => 'bbb'])
            ->build();
        $adjusted = $this->adjuster->adjustCommand($query);
        $this->assertCount(2, $adjusted->getFacetsFilters());
        $this->assertEquals(1, $adjusted->getFacetsFilters()[0][0]->getId());
        $this->assertEquals('aaa', $adjusted->getFacetsFilters()[0][1]);
        $this->assertEquals('kindId', $adjusted->getFacetsFilters()[1][0]);
        $this->assertEquals('bbb', $adjusted->getFacetsFilters()[1][1]);
    }
}
