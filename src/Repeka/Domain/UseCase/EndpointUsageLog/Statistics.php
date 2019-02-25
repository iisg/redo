<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

class Statistics {
    private $usageKey;
    private $statisticsEntries;

    /**
     * @param string $usageKey
     * @param StatisticEntry[] $statisticsEntries
     */
    public function __construct(string $usageKey = '', array $statisticsEntries = []) {
        $this->usageKey = $usageKey;
        $this->statisticsEntries = $statisticsEntries;
    }

    public function getUsageKey(): string {
        return $this->usageKey;
    }

    /**
     * @return StatisticEntry[]
     */
    public function getStatisticsEntries(): array {
        return $this->statisticsEntries;
    }
}
