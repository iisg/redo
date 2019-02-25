<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

class StatisticsQueryBuilder {
    private $dateFrom = "";
    private $dateTo = "";

    public function build(): StatisticsQuery {
        return new StatisticsQuery($this->dateFrom, $this->dateTo);
    }

    public function filterByDateFrom($dateFrom): self {
        $this->dateFrom = $dateFrom;
        return $this;
    }

    public function filterByDateTo($dateTo): self {
        $this->dateTo = $dateTo;
        return $this;
    }
}
