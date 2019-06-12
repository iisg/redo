<?php
namespace Repeka\Domain\Repository;

use Repeka\Domain\Entity\EventLogEntry;

interface EventLogRepository {
    /** @return EventLogEntry[] */
    public function findAll();

    public function save(EventLogEntry $eventLogEntry): EventLogEntry;

    /** @return array [ ['stat_month' => string, 'usage_key' => string, 'monthly_sum' => integer] ] */
    public function getUsageStatistics(string $dateFrom, string $dateTo): array;

    /** @return array [ ['stat_month' => string, 'monthly_sum' => integer] ] */
    public function getRequestsStatistics(string $dateFrom, string $dateTo): array;

    /** @return array [ ['stat_month' => string, 'client_ip' => string, 'monthly_sum' => integer] ] */
    public function getIpStatistics(string $dateFrom, string $dateTo): array;
}
