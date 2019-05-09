<?php
namespace Repeka\Tests\Integration\Repository;

use Repeka\Domain\Repository\EndpointUsageLogRepository;
use Repeka\Domain\UseCase\EndpointUsageLog\EndpointUsageLogCreateCommand;
use Repeka\Tests\Integration\Traits\FixtureHelpers;
use Repeka\Tests\IntegrationTestCase;

/** @small */
class EndpointUsageLogDoctrineRepositoryIntegrationTest extends IntegrationTestCase {
    use FixtureHelpers;

    /** @var EndpointUsageLogRepository */
    private $endpointUsageLogEntryRepository;

    public function initializeDatabaseForTests() {
        $this->endpointUsageLogEntryRepository = $this->container->get(EndpointUsageLogRepository::class);
        parent::setUp();
        $this->fillDataBase();
    }

    private function fillDataBase() {
        $client = self::createAdminClient();
        $client->apiRequest('GET', '');
        $this->handleCommandBypassingFirewall(new EndpointUsageLogCreateCommand($client->getRequest(), 'resourceDetails'));
        $this->handleCommandBypassingFirewall(new EndpointUsageLogCreateCommand($client->getRequest(), 'resourceDetails'));
        $this->handleCommandBypassingFirewall(new EndpointUsageLogCreateCommand($client->getRequest(), 'home'));
        $this->handleCommandBypassingFirewall(new EndpointUsageLogCreateCommand($client->getRequest(), 'bibtex'));
    }

    public function testFindAll() {
        $statistics = $this->endpointUsageLogEntryRepository->findAll();
        $this->assertCount(6, $statistics);     // one log is from creating admin client and one from adding session endpoint
    }

    public function testGetStatisticsFromCurrentMonth() {
        $dateFrom = date(DATE_ATOM, strtotime("-1 month"));
        $dateTo = date(DATE_ATOM, strtotime("+ 1 month"));
        $statistics = $this->endpointUsageLogEntryRepository->getUsageStatistics($dateFrom, $dateTo);
        $this->assertArrayHasAllValues(
            ['resourceDetails', 'home', 'bibtex', 'sessions'],
            array_column($statistics, 'usage_key')
        );
        $this->assertCount(4, $statistics);
    }
}
