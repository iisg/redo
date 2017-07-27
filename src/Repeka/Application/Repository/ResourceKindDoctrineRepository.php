<?php
namespace Repeka\Application\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
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
    public function findAllSystemResourceKinds(): array {
        $criteria = Criteria::create()
            ->orWhere(Criteria::expr()->lt('id', 0));
        $result = $this->matching($criteria);
        return $result->toArray();
    }

    /** @return ResourceKind[] */
    public function findAllByResourceClass(string $resourceClass): array {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('resourceClass', $resourceClass));
        $result = $this->matching($criteria);
        return $result->toArray();
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
