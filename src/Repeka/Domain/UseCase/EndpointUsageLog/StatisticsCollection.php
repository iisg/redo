<?php
namespace Repeka\Domain\UseCase\EndpointUsageLog;

class StatisticsCollection {
    /** @var int */
    private $resourcesCount;
    /** @var int */
    private $openResourcesCount;
    /** @var Statistics[] */
    private $statistics = [];

    public function __construct(int $resourcesCount = 0, int $openResourcesCount = 0) {
        $this->resourcesCount = $resourcesCount;
        $this->openResourcesCount = $openResourcesCount;
    }

    public function addStatistics(Statistics $statistics) {
        $this->statistics[] = $statistics;
    }

    public function getResourcesCount(): int {
        return $this->resourcesCount;
    }

    public function getOpenResourcesCount(): int {
        return $this->openResourcesCount;
    }

    /** @return Statistics[] */
    public function getStatistics(): array {
        return $this->statistics;
    }
}
