<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Entity\EventLogEntry;
use Repeka\Domain\Repository\EventLogRepository;

class EventLogDoctrineRepository extends EntityRepository implements EventLogRepository {

    public function save(EventLogEntry $eventLogEntry): EventLogEntry {
        $this->getEntityManager()->persist($eventLogEntry);
        return $eventLogEntry;
    }

    public function getUsageStatistics(string $dateFrom, string $dateTo): array {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::scalar('stat_month', 'string')
            ->addScalarResult('event_name', 'usage_key', 'string')
            ->addScalarResult('monthly_sum', 'monthly_sum', 'integer');
        $query = $em->createNativeQuery(
            <<<SQL
        SELECT date_trunc('month', event_date_time) AS stat_month, event_name, count(event_name) AS monthly_sum
          FROM event_log
          WHERE date_trunc('month', event_date_time) > date_trunc('month', :fromDate::timestamp)
            AND date_trunc('month', event_date_time) < date_trunc('month', :toDate::timestamp)
          GROUP BY stat_month, event_name
SQL
            ,
            $resultSetMapping
        );
        $query->setParameters(['fromDate' => $dateFrom, 'toDate' => $dateTo]);
        return $query->getArrayResult();
    }

    public function getRequestsStatistics(string $dateFrom, string $dateTo): array {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::scalar('stat_month', 'string')
            ->addScalarResult('monthly_sum', 'monthly_sum', 'integer');
        $query = $em->createNativeQuery(
            <<<SQL
        SELECT date_trunc('month', event_date_time) AS stat_month, count(client_ip) as monthly_sum
           FROM endpoint_usage_log 
           WHERE date_trunc('month', event_date_time) > date_trunc('month', :fromDate::timestamp)
               AND date_trunc('month', event_date_time) < date_trunc('month', :toDate::timestamp)
           GROUP BY stat_month
SQL
            ,
            $resultSetMapping
        );
        $query->setParameters(['fromDate' => $dateFrom, 'toDate' => $dateTo]);
        return $query->getArrayResult();
    }

    public function getIpStatistics(string $dateFrom, string $dateTo): array {
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::scalar('stat_month', 'string')
            ->addScalarResult('client_ip', 'client_ip', 'string')
            ->addScalarResult('monthly_sum', 'monthly_sum', 'integer');
        $query = $em->createNativeQuery(
            <<<SQL
        SELECT date_trunc('month', event_date_time) AS stat_month, client_ip, count(client_ip) as monthly_sum
          FROM endpoint_usage_log 
          WHERE date_trunc('month', event_date_time) > date_trunc('month', :fromDate::timestamp)
            AND date_trunc('month', event_date_time) < date_trunc('month', :toDate::timestamp) 
            AND client_ip IN (SELECT client_ip 
                                FROM endpoint_usage_log
                                WHERE date_trunc('month', event_date_time) > date_trunc('month', :fromDate::timestamp)
                                  AND date_trunc('month', event_date_time) < date_trunc('month', :toDate::timestamp) 
                                GROUP BY client_ip LIMIT 10)
          GROUP BY stat_month, client_ip 
SQL
            ,
            $resultSetMapping
        );
        $query->setParameters(['fromDate' => $dateFrom, 'toDate' => $dateTo]);
        return $query->getArrayResult();
    }
}
