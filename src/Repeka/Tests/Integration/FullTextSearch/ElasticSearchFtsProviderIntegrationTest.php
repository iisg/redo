<?php
namespace Repeka\Tests\Integration\FullTextSearch;

use Elastica\Result;
use Elastica\ResultSet;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Entity\Metadata;
use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\MetadataRepository;
use Repeka\Domain\UseCase\Resource\ResourceListFtsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class ElasticSearchFtsProviderIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var Metadata */
    private $titleMetadata;

    /** @var Metadata */
    private $opisMetadata;

    /** @var Metadata */
    private $flexibleDateMetadata;

    /** @var Metadata */
    private $timestampMetadata;

    /** @var Metadata */
    private $rangeDateMetadata;

    /** @var ResourceEntity */
    private $phpBookResource;

    /** @var ResourceEntity */
    private $phpAndMySQLBookResource;

    /** @var ResourceEntity */
    private $pythonBookResource;

    /** @var ResourceEntity */
    private $timeResource;

    /** @var ResourceEntity */
    private $alaResource;

    /** @var ResourceEntity */
    private $pseudoAlaResource;

    private $title = 'ala ma psa';

    protected function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->titleMetadata = $this->findMetadataByName('Tytul');
        $this->opisMetadata = $this->findMetadataByName('opis');
        $this->alaResource = $this->createResource(
            $this->getPhpBookResource()->getKind(),
            [$this->titleMetadata->getId() => [$this->title]]
        );
        $this->executeCommand('repeka:evaluate-display-strategies');
        $this->executeCommand('repeka:fts:initialize');
        $this->timestampMetadata = $this->createMetadata(
            'timestamp_example',
            ['PL' => 'timestamp', 'EN' => 'timestamp'],
            [],
            [],
            MetadataControl::TIMESTAMP
        );
        $this->flexibleDateMetadata = $this->createMetadata(
            'flexibleDate_example',
            ['PL' => 'flexibleDate', 'EN' => 'flexibleDate'],
            [],
            [],
            MetadataControl::FLEXIBLE_DATE
        );
        $this->rangeDateMetadata = $this->createMetadata(
            'rangeDate_example',
            ['PL' => 'rangeDate', 'EN' => 'rangeDate'],
            [],
            [],
            MetadataControl::DATE_RANGE
        );
        $timeResourceKind = $this->createResourceKind(
            'timeKind',
            ['PL' => 'timeKind', 'EN' => 'timeKind'],
            [$this->timestampMetadata, $this->flexibleDateMetadata, $this->rangeDateMetadata]
        );
        $this->timeResource = $this->createResource(
            $timeResourceKind,
            [
                $this->flexibleDateMetadata->getId() => [
                    [
                        'value' => [
                            "from" => "1999-06-01T00:00:00",
                            "to" => "1999-06-30T23:59:59",
                            "mode" => "range",
                            "rangeMode" => 'day',
                            "displayValue" => "06.1996",
                        ],
                    ],
                ],
                $this->timestampMetadata->getId() => [["value" => "2001-12-05T13:59:44+00:00"]],
                $this->rangeDateMetadata->getId() => [
                    [
                        'value' => [
                            "from" => "1999-06-01T00:00:00",
                            "to" => null,
                            'mode' => 'range',
                            "rangeMode" => 'day',
                        ],
                    ],
                ],
            ]
        );
        $this->pseudoAlaResource = $this->createResource(
            $this->getPhpBookResource()->getKind(),
            [
                $this->titleMetadata->getId() => ['ola ela ara'],
                $this->opisMetadata->getId() => ['aba ila ata'],
            ]
        );
    }

    /** @before */
    public function fetchData() {
        $this->phpBookResource = $this->findResourceByContents(['Tytul' => 'PHP - to można leczyć!']);
        $this->pythonBookResource = $this->findResourceByContents(['Tytul' => 'Python dla opornych']);
        $this->phpAndMySQLBookResource = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL']);
        $metadataRepository = $this->container->get(MetadataRepository::class);
        $this->timestampMetadata = $metadataRepository->findByName('timestamp_example');
        $this->flexibleDateMetadata = $metadataRepository->findByName('flexibleDate_example');
        $this->rangeDateMetadata = $metadataRepository->findByName('rangeDate_example');
    }

    public function testSearchByPhpPhrase() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('PHP', [SystemMetadata::RESOURCE_LABEL]));
        $ids = EntityUtils::mapToIds($results);
        $this->assertContains($this->phpBookResource->getId(), $ids);
        $this->assertContains($this->phpAndMySQLBookResource->getId(), $ids);
    }

    public function testSearchByPhpAndPoradnikPhrase() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php poradnik', [SystemMetadata::RESOURCE_LABEL, $this->opisMetadata])
        );
        $ids = EntityUtils::mapToIds($results);
        $this->assertCount(1, $ids);
        $this->assertContains($this->phpBookResource->getId(), $ids);
    }

    public function testSearchByAdvancedPhrase() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('(PAP~1 | oporny) -MySQL', [SystemMetadata::RESOURCE_LABEL])
        );
        $ids = EntityUtils::mapToIds($results);
        $this->assertEquals(2, sizeof($ids));
        $this->assertContains($this->phpBookResource->getId(), $ids);
        $this->assertContains($this->pythonBookResource->getId(), $ids);
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
        $resultsAll = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('php', [SystemMetadata::RESOURCE_LABEL]));
        $resultsInDictionaries = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('php', [SystemMetadata::RESOURCE_LABEL], [], ['dictionaries'])
        );
        $this->assertLessThan(count($resultsAll), count($resultsInDictionaries));
    }

    public function testSearchStopWords() {
        /** @var Result[] $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('i', [SystemMetadata::RESOURCE_LABEL]));
        $this->assertEmpty($results);
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
            new ResourceListFtsQuery('php | python', ['tytul'], [], [], true, ['powiazanaKsiazka'])
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
            new ResourceListFtsQuery('php | python', ['tytul'], [], [], true, ['powiazanaKsiazka'], ['powiazanaKsiazka' => [$phpIMysqlId]])
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
            new ResourceListFtsQuery('php | python', ['tytul'], [], [], true, ['powiazanaKsiazka'], ['kindId' => [2]])
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
                'php | python',
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

    public function testFindAll() {
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('', [], [], ['books']));
        $this->assertCount(9, $results);
    }

    public function testFindOnlyTopLevel() {
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('', [], [], ['books'], false, [], [], true));
        $this->assertCount(7, $results);
    }

    public function testFilteringByTimestamp() {
        $id = $this->timestampMetadata->getId();
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('', [$id], [$id => ['from' => '2000-11-21T11:40:09+00:00']])
        );
        $this->assertCount(1, $results);
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('', [$id], [$id => ['to' => '2002-11-21T11:40:09+00:00']])
        );
        $this->assertCount(1, $results);
        $results = $this->handleCommandBypassingFirewall(
            new ResourceListFtsQuery('', [$id], [$id => ['from' => '2000-11-21T11:40:09+00:00', 'to' => '2002-11-21T11:40:09+00:00']])
        );
        $this->assertCount(1, $results);
    }

    public function testFilteringByFlexibleDate() {
        $id = $this->flexibleDateMetadata->getId();
        foreach ([   // searched range 1999-06-01T00:00:00 -
                     [1, ['from' => '1998-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > | | TRUE
                     [1, ['from' => '1999-06-21T11:40:09+00:00', 'rangeMode' => 'date_time']],
                     // | > | TRUE
                     [0, ['from' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | | > FALSE
                     [1, ['to' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | | < TRUE
                     [1, ['to' => '1999-06-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | < | TRUE
                     [0, ['to' => '1998-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // < | | FALSE
                     [1, ['from' => '1998-11-21T11:40:09+00:00', 'to' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > | | < TRUE
                     [1, ['from' => '1999-06-11T11:40:09+00:00', 'to' => '1999-06-22T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | > < | TRUE
                     [1, ['from' => '1999-06-11T11:40:09+00:00', 'to' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | > | < TRUE
                     [1, ['from' => '1998-11-21T11:40:09+00:00', 'to' => '1999-06-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > | < | TRUE
                     [0, ['from' => '2100-11-21T11:40:09+00:00', 'to' => '2200-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | | > < FALSE
                     [0, ['from' => '1700-11-21T11:40:09+00:00', 'to' => '1800-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     //> < | | FALSE
                 ] as $testCase) {
            list($expectedCount, $range) = $testCase;
            /** @var ResultSet $results */
            $results = $this->handleCommandBypassingFirewall(
                new ResourceListFtsQuery('', [$id], [$id => $range])
            );
            $this->assertCount($expectedCount, $results);
        }
    }

    public function testFilteringByRangeDate() {
        $id = $this->rangeDateMetadata->getId();
        foreach ([   // searched range 1999-06-01T00:00:00 -
                     [1, ['from' => '1998-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > | oo TRUE
                     [1, ['from' => '1999-06-21T11:40:09+00:00', 'rangeMode' => 'date_time']],
                     // | > oo TRUE
                     [1, ['from' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | oo > TRUE
                     [1, ['to' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | oo < TRUE
                     [1, ['to' => '1999-06-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | < oo TRUE
                     [0, ['to' => '1998-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // < | oo FALSE
                     [1, ['from' => '1998-11-21T11:40:09+00:00', 'to' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > | oo < TRUE
                     [1, ['from' => '1999-06-11T11:40:09+00:00', 'to' => '1999-06-22T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | > < oo TRUE
                     [1, ['from' => '1999-06-11T11:40:09+00:00', 'to' => '2000-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | > oo < TRUE
                     [1, ['from' => '1998-11-21T11:40:09+00:00', 'to' => '1999-06-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > | < oo TRUE
                     [1, ['from' => '2100-11-21T11:40:09+00:00', 'to' => '2200-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // | oo > < TRUE
                     [0, ['from' => '1700-11-21T11:40:09+00:00', 'to' => '1800-11-21T11:40:09+00:00', 'rangeMode' => 'day']],
                     // > < | oo FALSE
                 ] as $testCase) {
            list($expectedCount, $range) = $testCase;
            /** @var ResultSet $results */
            $results = $this->handleCommandBypassingFirewall(
                new ResourceListFtsQuery('', [$id], [$id => $range])
            );
            $this->assertCount($expectedCount, $results);
        }
    }

    public function testBoostToSimpleQueryString() {
        /** @var ResultSet $results */
        $results = $this->handleCommandBypassingFirewall(new ResourceListFtsQuery('ala', [$this->titleMetadata, $this->opisMetadata]));
        $this->assertCount(2, $results);
        $this->assertEquals($this->alaResource->getId(), intval($results[0]->getId()));
        $this->assertEquals($this->pseudoAlaResource->getId(), intval($results[1]->getId()));
    }

    /**
     * Aggregations are nested twice, because of the filters applied. This method extracts them.
     * @see https://madewithlove.be/faceted-search-using-elasticsearch/
     */
    private function extractAggregation(ResultSet $resultSet, string $aggregationName): array {
        return current($resultSet->getAggregation($aggregationName)['buckets'])[$aggregationName]['buckets'];
    }
}
