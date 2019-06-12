<?php
namespace Repeka\Domain\UseCase\Stats;

class StatisticEntry {
    private $statMonth;
    private $monthlySum;
    private $usageKey;
    private $clientIp;

    public function __construct(
        string $statMonth = '',
        int $monthlySum = null,
        string $usageKey = '',
        string $clientIp = ''
    ) {
        $this->statMonth = $statMonth;
        $this->monthlySum = $monthlySum;
        $this->usageKey = $usageKey;
        $this->clientIp = $clientIp;
    }

    public function getStatMonth(): string {
        return $this->statMonth;
    }

    public function getMonthlySum(): int {
        return $this->monthlySum;
    }

    public function getUsageKey(): string {
        return $this->usageKey;
    }

    public function getClientIp(): string {
        return $this->clientIp;
    }
}
