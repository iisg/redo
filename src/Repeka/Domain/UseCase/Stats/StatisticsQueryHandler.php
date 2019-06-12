<?php
namespace Repeka\Domain\UseCase\Stats;

use Repeka\Domain\Entity\StatisticsBucket;
use Repeka\Domain\Repository\EventLogRepository;

class StatisticsQueryHandler {
    /** @var EventLogRepository */
    private $eventLogRepository;

    public function __construct(EventLogRepository $eventLogRepository) {
        $this->eventLogRepository = $eventLogRepository;
    }

    /** @return StatisticsBucket[] */
    public function handle(StatisticsQuery $query): array {
        return $this->eventLogRepository->getStatistics($query);
    }
}
