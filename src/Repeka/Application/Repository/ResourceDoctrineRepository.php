<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Constants\SystemMetadata;
use Repeka\Domain\Constants\SystemResourceKind;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceKind;
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
    public function findTopLevel(): array {
        $parentMetadataId = SystemMetadata::PARENT;
        $qb = $this->createQueryBuilder('r');
        return $qb->join('r.kind', 'rk')
            ->where("JSON_GET(r.contents, '$parentMetadataId') = '[]' OR JSONB_EXISTS(r.contents, '$parentMetadataId') = FALSE")
            ->andWhere($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->getQuery()
            ->getResult();
    }

    /** @return ResourceEntity[] */
    public function findChildren(int $parentId): array {
        $parentMetadataId = SystemMetadata::PARENT;
        return $this->createQueryBuilder('r')
            ->where("JSONB_CONTAINS(JSON_GET(r.contents, '$parentMetadataId'), :parentId) = TRUE")
            ->setParameter('parentId', $parentId)
            ->getQuery()
            ->getResult();
    }

    /** @return ResourceEntity[] */
    public function findAllNonSystemResources(): array {
        $qb = $this->createQueryBuilder('r');
        return $qb->join('r.kind', 'rk')
            ->where($qb->expr()->notIn('rk.id', SystemResourceKind::values()))
            ->getQuery()
            ->getResult();
    }

    public function exists(int $resourceId): bool {
        return !!$this->find($resourceId);
    }

    public function delete(ResourceEntity $resource): void {
        $this->getEntityManager()->remove($resource);
    }

    public function countByResourceKind(ResourceKind $resourceKind): int {
        $qb = $this->createQueryBuilder('r');
        $query = $qb->select('COUNT(r.id)')
            ->where('r.kind = :kind')
            ->setParameter('kind', $resourceKind)
            ->getQuery();
        return $query->getSingleScalarResult();
    }
}
