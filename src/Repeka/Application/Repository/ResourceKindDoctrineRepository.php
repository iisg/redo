<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindDoctrineRepository extends EntityRepository implements ResourceKindRepository {
    public function save(ResourceKind $resourceKind): ResourceKind {
        $this->getEntityManager()->persist($resourceKind);
        return $resourceKind;
    }

    /** @return ResourceKind[] */
    public function findAllNonSystemResourceKinds(): array {
        $qb = $this->createQueryBuilder('rk');
        return $qb->where($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->getQuery()
            ->getResult();
    }

    public function findOne(int $id): ResourceKind {
        /** @var ResourceKind $resourceKind */
        $resourceKind = $this->find($id);
        if (!$resourceKind) {
            throw new EntityNotFoundException($this, $id);
        }
        return $resourceKind;
    }

    public function exists(int $id): bool {
        return !!$this->find($id);
    }

    public function delete(ResourceKind $resourceKind): void {
        $this->getEntityManager()->remove($resourceKind);
    }

    public function countByResourceWorkflow(ResourceWorkflow $resourceWorkflow): int {
        $qb = $this->createQueryBuilder('rk');
        $query = $qb->select('COUNT(rk.id)')
            ->where('rk.workflow = :workflow')
            ->setParameter('workflow', $resourceWorkflow)
            ->getQuery();
        return $query->getSingleScalarResult();
    }
}
