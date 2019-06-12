<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\EventLogEntry;
use Repeka\Domain\Entity\StatisticsBucket;
use Repeka\Domain\Factory\StatisticsQuerySqlFactory;
use Repeka\Domain\Repository\EventLogRepository;
use Repeka\Domain\UseCase\Stats\StatisticsQuery;

class EventLogDoctrineRepository extends EntityRepository implements EventLogRepository {

    public function save(EventLogEntry $eventLogEntry): EventLogEntry {
        $this->getEntityManager()->persist($eventLogEntry);
        return $eventLogEntry;
    }

    public function getStatistics(StatisticsQuery $query): array {
        $sqlFactory = new StatisticsQuerySqlFactory($query);
        $em = $this->getEntityManager();
        $sqlQuery = $em->createNativeQuery($sqlFactory->getSelectQuery(), $sqlFactory->getResultSetMapping());
        $sqlQuery->setParameters($sqlFactory->getParams());
        return array_map(
            function (array $row) use ($query) {
                return new StatisticsBucket($row, $query->getAggregation());
            },
            $sqlQuery->getArrayResult()
        );
    }
}
