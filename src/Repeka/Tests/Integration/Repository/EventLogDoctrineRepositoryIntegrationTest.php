<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Domain\UseCase\Stats\EventLogCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class EventLogDoctrineRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EventLogRepository */
    private $eventLogRepository;

    public function initializeDatabaseForTests() {
        $this->eventLogRepository = $this->container->get(EventLogRepository::class);
        parent::setUp();
        $this->fillDataBase();
    }

    private function fillDataBase() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', '');
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('resourceDetails'));
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('resourceDetails'));
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('home'));
        $this->handleCommandBypassingFirewall(new EventLogCreateCommand('bibtex'));
    }

    public function testFindAll() {
        $statistics = $this->eventLogRepository->findAll();
        $this->assertCount(6, $statistics);     // one log is from creating admin client and one from adding session endpoint
    }

    public function testGetStatisticsFromCurrentMonth() {
        $dateFrom = date(DATE_ATOM, strtotime("-1 month"));
        $dateTo = date(DATE_ATOM, strtotime("+ 1 month"));
        $statistics = $this->eventLogRepository->getUsageStatistics($dateFrom, $dateTo);
        $this->assertArrayHasAllValues(
            ['resourceDetails', 'home', 'bibtex', 'sessions'],
            array_column($statistics, 'usage_key')
        );
        $this->assertCount(4, $statistics);
    }
}
