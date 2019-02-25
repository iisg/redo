<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AdjustableCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;

class StatisticsQuery extends AbstractCommand implements NonValidatedCommand, AdjustableCommand {
    private $dateFrom;
    private $dateTo;

    public function __construct(
        string $dateFrom,
        string $dateTo
    ) {
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function getDateFrom(): string {
        return $this->dateFrom;
    }

    public function getDateTo(): string {
        return $this->dateTo;
    }

    public static function builder(): StatisticsQueryBuilder {
        return new StatisticsQueryBuilder();
    }
}
