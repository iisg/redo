<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResource;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceContents;
use Repeka\Domain\Repository\EndpointUsageLogRepository;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Service\UnauthenticatedUserPermissionHelper;
use Repeka\Domain\UseCase\PageResult;
use Repeka\Domain\UseCase\Resource\ResourceListQuery;

class StatisticsQueryHandler {
    /** @var EndpointUsageLogRepository */
    private $endpointUsageLogEntryRepository;
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var CommandBus */
    private $commandBus;
    /**
     * @var UnauthenticatedUserPermissionHelper
     */
    private $unauthenticatedUserPermissionHelper;

    public function __construct(
        CommandBus $commandBus,
        EndpointUsageLogRepository $endpointUsageLogEntryRepository,
        ResourceRepository $resourceRepository,
        UnauthenticatedUserPermissionHelper $unauthenticatedUserPermissionHelper
    ) {
        $this->endpointUsageLogEntryRepository = $endpointUsageLogEntryRepository;
        $this->resourceRepository = $resourceRepository;
        $this->commandBus = $commandBus;
        $this->unauthenticatedUserPermissionHelper = $unauthenticatedUserPermissionHelper;
    }

    public function handle(StatisticsQuery $query): StatisticsCollection {
        /** @var PageResult $resourcesCount */
        $resourcesCount = $this->resourceRepository->count([]);
        /** @var PageResult $openResources */
        $openResources = $this->commandBus->handle(
            ResourceListQuery::builder()
                ->setExecutor($this->unauthenticatedUserPermissionHelper->getUnauthenticatedUser())
                ->setPage(1)
                ->setResultsPerPage(1)
                ->build()
        );
        $statistics = new StatisticsCollection($resourcesCount, $openResources->getTotalCount());
        $stats = $this->endpointUsageLogEntryRepository->getUsageStatistics($query->getDateFrom(), $query->getDateTo());
        $this->addStatistics($statistics, $stats);
        $requestStats = $this->endpointUsageLogEntryRepository->getRequestsStatistics($query->getDateFrom(), $query->getDateTo());
        $this->addStatistics($statistics, $requestStats, 'requests');
        $ipStats = $this->endpointUsageLogEntryRepository->getIpStatistics($query->getDateFrom(), $query->getDateTo());
        $this->addStatistics($statistics, $ipStats, 'ips');
        return $statistics;
    }

    /**
     * @param StatisticsCollection $statisticsCollection
     * @param mixed $entries
     * @param string | null $name
     */
    private function addStatistics(StatisticsCollection &$statisticsCollection, array $entries, string $name = null) {
        /** @var StatisticEntry[] $entries */
        $entries = array_map(
            function ($entry) {
                return new StatisticEntry(
                    $entry['stat_month'] ?? '',
                    $entry['monthly_sum'] ?? null,
                    $entry['usage_key'] ?? '',
                    $entry['client_ip'] ?? ''
                );
            },
            $entries
        );
        if ($name) {
            $statistics = new Statistics($name, $entries);
            $statisticsCollection->addStatistics($statistics);
        } else {
            $statisticsMap = [];
            foreach ($entries as $entry) {
                $usageKey = $entry->getUsageKey();
                if (!array_key_exists($usageKey, $statisticsMap)) {
                    $statisticsMap[$usageKey] = [$entry];
                } else {
                    $statisticsMap[$usageKey][] = $entry;
                }
            }
            foreach ($statisticsMap as $usageKey => $entries) {
                $statistics = new Statistics($usageKey, $entries);
                $statisticsCollection->addStatistics($statistics);
            }
        }
    }
}
