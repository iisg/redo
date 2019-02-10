<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Domain\Factory\AuditEntryListQuerySqlFactory;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\UseCase\Audit\AuditedCommandNamesQuery;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Domain\UseCase\PageResult;

class AuditEntryDoctrineRepository extends EntityRepository implements AuditEntryRepository {
    public function findByQuery(AuditEntryListQuery $query): PageResult {
        $queryFactory = new AuditEntryListQuerySqlFactory($query);
        $em = $this->getEntityManager();
        $resultSetMapping = ResultSetMappings::auditEntry($em);
        $dbQuery = $em->createNativeQuery($queryFactory->getPageQuery(), $resultSetMapping)->setParameters($queryFactory->getParams());
        $pageContents = $dbQuery->getResult();
        $total = $em->createNativeQuery($queryFactory->getTotalCountQuery(), ResultSetMappings::scalar())
            ->setParameters($queryFactory->getParams());
        $total = (int)$total->getSingleScalarResult();
        return new PageResult($pageContents, $total, $query->getPage());
    }

    public function getAuditedCommandNames(AuditedCommandNamesQuery $query): array {
        $dbQuery = $this->createQueryBuilder('a')
            ->select('a.commandName')
            ->distinct();
        if ($query->getOnlyResource()) {
            $dbQuery = $dbQuery->where("a.commandName LIKE 'resource_%'");
        }
        $dbQuery = $dbQuery->getQuery();
        $commandNames = $dbQuery->getArrayResult();
        return array_column($commandNames, 'commandName');
    }
}
