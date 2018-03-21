<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Repeka\Domain\Repository\AuditEntryRepository;
use Repeka\Domain\UseCase\Audit\AuditEntryListQuery;
use Repeka\Domain\UseCase\PageResult;

class AuditEntryDoctrineRepository extends EntityRepository implements AuditEntryRepository {
    public function findByQuery(AuditEntryListQuery $query): PageResult {
        $qb = $this->createQueryBuilder('ae');
        if ($query->getCommandNames()) {
            $qb->where($qb->expr()->in('ae.commandName', ':commandNames'));
            $qb->setParameter('commandNames', $query->getCommandNames());
        }
        if ($query->paginate()) {
            $offset = ($query->getPage() - 1) * $query->getResultsPerPage();
            $qb->setFirstResult($offset);
        }
        $qb->setMaxResults($query->getResultsPerPage());
        $qb->orderBy('ae.createdAt', 'DESC');
        $paginator = new Paginator($qb);
        $results = $paginator->getQuery()->getResult();
        return new PageResult($results, $paginator->count(), $query->getPage());
    }

    public function getAuditedCommandNames(): array {
        $query = $this->createQueryBuilder('a')
            ->select('a.commandName')
            ->distinct()
            ->getQuery();
        $commandNames = $query->getArrayResult();
        return array_column($commandNames, 'commandName');
    }
}
