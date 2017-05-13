<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\EntityNotFoundException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceDoctrineRepository extends EntityRepository implements ResourceRepository {
    public function save(ResourceEntity $resource): ResourceEntity {
        $this->getEntityManager()->persist($resource);
        return $resource;
    }

    public function findOne(int $id): ResourceEntity {
        /** @var ResourceEntity $resource */
        $resource = $this->find($id);
        if (!$resource) {
            throw new EntityNotFoundException($this, $id);
        }
        return $resource;
    }

    /** @return ResourceEntity[] */
    public function findAllNonSystemResources(): array {
        $qb = $this->createQueryBuilder('r');
        return $qb->join('r.kind', 'rk')
            ->where($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->getQuery()
            ->getResult();
    }
}
