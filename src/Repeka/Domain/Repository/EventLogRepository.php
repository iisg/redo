<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\EventLogEntry;
use Repeka\Domain\Entity\StatisticsBucket;
use Repeka\Domain\UseCase\Stats\StatisticsQuery;

interface EventLogRepository {
    /** @return EventLogEntry[] */
    public function findAll();

    public function save(EventLogEntry $eventLogEntry): EventLogEntry;

    /** @return StatisticsBucket[] */
    public function getStatistics(StatisticsQuery $query): array;
}
