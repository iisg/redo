<?php
namespace Repeka\Tests\Integration\FullTextSearch;

use Elastica\Result;
use Elastica\ResultSet;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCaseWithoutDroppingDatabase;

class ElasticSearchFtsProviderIntegrationTest extends IntegrationTestCaseWithoutDroppingDatabase {
    use FixtureHelpers;

    /** @var ResourceEntity */
    private $phpBookResource;

    /** @var ResourceEntity */
    private $phpAndMySQLBookResource;

    private $title = 'ala ma psa';

    protected function initializeDatabaseBeforeTheFirstTest() {
        $this->loadAllFixtures();
        $metadata = $this->findMetadataByName('Tytul');
        $this->createResource($this->getPhpBookResource()->getKind(), [$metadata->getId() => [$this->title]]);
        $this->executeCommand('repeka:evaluate-display-strategies');
        $this->executeCommand('repeka:fts:initialize');
    }

    /** @before */
    public function fetchResources() {
        $this->phpBookResource = $this->findResourceByContents(['Tytul' => 'PHP - to można leczyć!']);
        $this->phpAndMySQLBookResource = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL']);
    }

    public function testSearchByPhpPhrase() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('PHP', [SystemMetadata::RESOURCE_LABEL]));
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpBookResource->getId(), $ids);
        $this->assertContains($this->phpAndMySQLBookResource->getId(), $ids);
    }

    public function testHighlightingPhpPhrase() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('PHP', [SystemMetadata::RESOURCE_LABEL]));
        $highlights = current($results[0]->getHighlights());
        $this->assertContains('<em>PHP</em>', $highlights[0]);
    }

    public function testSearchByMysql() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('mysql', [SystemMetadata::RESOURCE_LABEL]));
        $ids = EntityUtils::mapToIds($results);
        $this->assertNotContains($this->phpBookResource->getId(), $ids);
        $this->assertContains($this->phpAndMySQLBookResource->getId(), $ids);
    }

    public function testSearchByPhpPhraseInDifferentMetadata() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('PHP', [SystemMetadata::USERNAME]));
        $this->assertEmpty($results);
    }

    public function testSearchSubmetadata() {
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('więcej', ['urlLabel']));
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpBookResource->getId(), $ids);
    }

    public function testSearchOnlyInGivenResourceClasses() {
        $resultsAll = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('i', [SystemMetadata::RESOURCE_LABEL]));
        $resultsInBooks = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('i', [SystemMetadata::RESOURCE_LABEL], [], ['books'])
        );
        $this->assertLessThan(count($resultsAll), count($resultsInBooks));
        $ids = EntityUtils::mapToIds($resultsInBooks);
        $this->assertContains($this->phpAndMySQLBookResource->getId(), $ids);
    }

    public function testSearchWithPagination() {
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            ResourceListFtsQuery::builder()
                ->setPhrase('PHP')
                ->setSearchableMetadata([SystemMetadata::RESOURCE_LABEL])
                ->setPage(1)
                ->setResultsPerPage(1)
                ->build()
        );
        $this->assertCount(1, $results);
        $this->assertEquals(2, $results->getTotalHits());
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpAndMySQLBookResource->getId(), $ids);
    }

    public function testQueryTheSecondPage() {
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            ResourceListFtsQuery::builder()
                ->setPhrase('PHP')
                ->setSearchableMetadata([SystemMetadata::RESOURCE_LABEL])
                ->setPage(2)
                ->setResultsPerPage(1)
                ->build()
        );
        $this->assertCount(1, $results);
        $this->assertEquals(2, $results->getTotalHits());
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpBookResource->getId(), $ids);
    }

    public function testQueryWithTypo() {
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('wiecej', ['urlLabel']));
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpBookResource->getId(), $ids);
    }

    public function testSearchWithPolishVariety() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('PIES', [SystemMetadata::RESOURCE_LABEL]));
        $ids = EntityUtils::mapToIds($results);
        $newResource = $this->findResourceByContents(['Tytul' => $this->title]);
        $this->assertContains($newResource->getId(), $ids);
    }

    public function testSearchFacets() {
        $powiazanaKsiazka = $this->findMetadataByName('powiazanaKsiazka');
        $phpIMysqlId = $this->findResourceByContents(['tytul' => 'PHP i MySQL'])->getId();
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php python', ['tytul'], [], [], true, ['powiazanaKsiazka'])
        );
        $kindIdAggregations = $this->extractAggregation($results, 'kindId');
        $this->assertEquals([['key' => 1, 'doc_count' => 2], ['key' => 2, 'doc_count' => 1]], $kindIdAggregations);
        $powiazanaKsiazkaAggregations = $this->extractAggregation($results, $powiazanaKsiazka->getId());
        $this->assertEquals([['key' => $phpIMysqlId, 'doc_count' => 1]], $powiazanaKsiazkaAggregations);
    }

    public function testFilteringByFacets() {
        $powiazanaKsiazka = $this->findMetadataByName('powiazanaKsiazka');
        $phpIMysqlId = $this->findResourceByContents(['tytul' => 'PHP i MySQL'])->getId();
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php python', ['tytul'], [], [], true, ['powiazanaKsiazka'], ['powiazanaKsiazka' => [$phpIMysqlId]])
        );
        $this->assertCount(1, $results);
        $kindIdAggregations = $this->extractAggregation($results, 'kindId');
        $this->assertEquals([['key' => 1, 'doc_count' => 1]], $kindIdAggregations);
        $powiazanaKsiazkaAggregations = $this->extractAggregation($results, $powiazanaKsiazka->getId());
        $this->assertEquals([['key' => $phpIMysqlId, 'doc_count' => 1]], $powiazanaKsiazkaAggregations);
    }

    public function testFilteringClearNotexistentFacets() {
        $powiazanaKsiazka = $this->findMetadataByName('powiazanaKsiazka');
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php python', ['tytul'], [], [], true, ['powiazanaKsiazka'], ['kindId' => [2]])
        );
        $this->assertCount(1, $results);
        $kindIdAggregations = $this->extractAggregation($results, 'kindId');
        $this->assertEquals([['key' => 1, 'doc_count' => 2], ['key' => 2, 'doc_count' => 1]], $kindIdAggregations);
        $powiazanaKsiazkaAggregations = $this->extractAggregation($results, $powiazanaKsiazka->getId());
        $this->assertEmpty($powiazanaKsiazkaAggregations);
    }

    public function testMultipleFacetsNarrowSearchResults() {
        $phpIMysqlId = $this->findResourceByContents(['tytul' => 'PHP i MySQL'])->getId();
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery(
                'php python',
                ['tytul'],
                [],
                [],
                true,
                ['powiazanaKsiazka'],
                ['kindId' => [1], 'powiazanaKsiazka' => [$phpIMysqlId]]
            )
        );
        $this->assertCount(1, $results);
    }

    public function testFilteringByTextMetadata() {
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php', ['tytul'], ['tytul' => 'można'])
        );
        $this->assertCount(1, $results);
    }

    public function testFilteringByParentMetadata() {
        $ebooks = $this->findResourceByContents(['Nazwa kategorii' => 'E-booki']);
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php', ['tytul'], [SystemMetadata::PARENT => $ebooks->getId()])
        );
        $this->assertEmpty($results);
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('webpacka', ['tytul'], [SystemMetadata::PARENT => $ebooks->getId()])
        );
        $this->assertCount(1, $results);
    }

    public function testFilteringByRelationshipMetadata() {
        /** @var ResultSet $results */
        $this->assertEmpty(
            $this->handleCommandBypassingFirewall(
                new ResourceListFtsQuery('php', ['tytul'], ['skanista' => 2])
            )
        );
        $this->assertCount(
            2,
            $this->handleCommandBypassingFirewall(
                new ResourceListFtsQuery('php', ['tytul'], ['skanista' => 4])
            )
        );
    }

    public function testFilteringByManyMetadata() {
        $ebooks = $this->findResourceByContents(['Nazwa kategorii' => 'E-booki']);
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('webpacka', ['tytul'], [SystemMetadata::PARENT => $ebooks->getId(), 'tytul' => 'npma'])
        );
        $this->assertEmpty($results);
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('webpacka', ['tytul'], [SystemMetadata::PARENT => $ebooks->getId(), 'tytul' => 'użyć'])
        );
        $this->assertCount(1, $results);
    }

    /**
     * Aggregations are nested twice, because of the filters applied. This method extracts them.
     * @see https://madewithlove.be/faceted-search-using-elasticsearch/
     */
    private function extractAggregation(ResultSet $resultSet, string $aggregationName): array {
        return current($resultSet->getAggregation($aggregationName)['buckets'])[$aggregationName]['buckets'];
    }
}
