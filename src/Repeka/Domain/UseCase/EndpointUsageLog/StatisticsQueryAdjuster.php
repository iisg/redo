<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

use Repeka\Domain\Cqrs\Command;

class StatisticsQueryAdjuster {
    private const MOMENT_DATE_FORMAT = 'Y-m-d\TH:i:s';

    public function isDate(string $date): bool {
        return (date(self::MOMENT_DATE_FORMAT, strtotime($date)) == $date);
    }

    public function adjustDateFrom(string $dateFrom): string {
        return $this->isDate($dateFrom) ? $dateFrom : "";
    }

    public function adjustDateTo(string $dateTo): string {
        return $this->isDate($dateTo) ? date(self::MOMENT_DATE_FORMAT, strtotime('+1 day', strtotime($dateTo))) : "";
    }

    /** @param StatisticsQuery $command */
    public function adjustCommand(Command $command): Command {
        return new StatisticsQuery(
            $this->adjustDateFrom($command->getDateFrom()),
            $this->adjustDateTo($command->getDateTo())
        );
    }
}
