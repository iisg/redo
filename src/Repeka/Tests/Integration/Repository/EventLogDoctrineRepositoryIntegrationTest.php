<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Repeka\Domain\UseCase\Stats\StatisticsQuery;
use Repeka\Domain\Utils\EntityUtils;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\Integration\Traits\TestTimeProvider;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class EventLogDoctrineRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EventLogRepository */
    private $eventLogRepository;

    public function initializeDatabaseForTests() {
        $this->loadAllFixtures();
        $this->fillDataBase();
        $this->eventLogRepository = $this->container->get(EventLogRepository::class);
    }

    private function fillDataBase() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', '');
        $phpBook = $this->getPhpBookResource();
        $phpMysqlBook = $this->findResourceByContents(['Tytuł' => 'PHP i MySQL']);
        TestTimeProvider::setTime('2019-01-02');
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('resourceDetails', 'endpoint', $phpBook, $client->getRequest()));
        TestTimeProvider::setTime('2019-02-05');
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('resourceDetails', 'endpoint', $phpBook, $client->getRequest()));
        $this->handleCommandBypassingFirewall(
            new EventLogCreateCommand('resourceDetails', 'endpoint', $phpMysqlBook, $client->getRequest())
        );
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('home', 'endpoint', null, $client->getRequest()));
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('bibtex', 'cite', $phpBook, $client->getRequest()));
    }

    public function testFindAll() {
        $statistics = $this->eventLogRepository->findAll();
        $this->assertCount(7, $statistics); // one log is from creating admin client and one from adding session endpoint
    }

    public function testGettingAllStatistics() {
        $query = StatisticsQuery::builder()->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertCount(4, $stats);
        $stats = EntityUtils::getLookupMap($stats, 'eventName');
        $this->assertEquals(3, $stats['resourceDetails']['count']);
        $this->assertEquals('endpoint', $stats['resourceDetails']['eventGroup']);
        $this->assertEquals(2, $stats['home']['count']);
        $this->assertEquals(1, $stats['bibtex']['count']);
        $this->assertEquals(1, $stats['sessions']['count']);
    }

    public function testGettingAllStatisticsByMonth() {
        $query = StatisticsQuery::builder()->aggregateBy('month')->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $found = 0;
        foreach ($stats as $stat) {
            if ($stat['eventName'] == 'resourceDetails') {
                $found++;
                /** @var \DateTimeImmutable $date */
                $date = $stat['bucket']->format(\DateTime::ATOM);
                if (strpos($date, '2019-01-01') !== false) {
                    $this->assertEquals(1, $stat['count']);
                } else {
                    $this->assertEquals(2, $stat['count']);
                }
            }
        }
        $this->assertEquals(2, $found);
    }

    public function testGetStatisticsFromGivenDates() {
        $query = StatisticsQuery::builder()
            ->filterByDateFrom('2019-01-01')
            ->filterByDateTo('2019-01-10')
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertEquals(['resourceDetails'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(1, $stats);
    }

    public function testGetStatisticsFromGivenResourceId() {
        $query = StatisticsQuery::builder()
            ->filterByResourceId($this->getPhpBookResource()->getId())
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertArrayHasAllValues(['resourceDetails', 'bibtex'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(2, $stats);
    }

    public function testGetStatisticsFromGivenResourceContents() {
        $query = StatisticsQuery::builder()
            ->filterByResourceContents(['Tytuł' => 'PHP i MySQL'])
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertArrayHasAllValues(['resourceDetails'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(1, $stats);
    }

    public function testGetStatisticsFromGivenResourceKinds() {
        $query = StatisticsQuery::builder()
            ->filterByResourceKinds(['book'])
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertArrayHasAllValues(['resourceDetails', 'bibtex'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(2, $stats);
    }

    public function testGetStatisticsGroupedByResources() {
        $query = StatisticsQuery::builder()
            ->groupByResources()
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertArrayHasAllValues(['resourceDetails', 'bibtex'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(5, $stats);
    }

    public function testGetStatisticsGroupedByResourcesAndMonths() {
        $query = StatisticsQuery::builder()
            ->groupByResources()
            ->aggregateBy('month')
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertArrayHasAllValues(['resourceDetails', 'bibtex'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(7, $stats);
    }

    public function testFilterByEventGroup() {
        $query = StatisticsQuery::builder()
            ->filterByEventGroup('cite')
            ->build();
        $stats = $this->handleCommandBypassingFirewall($query);
        $this->assertEquals(['bibtex'], EntityUtils::mapToIds($stats, 'eventName'));
        $this->assertCount(1, $stats);
    }
}
