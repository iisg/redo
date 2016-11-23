<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Repository\ResourceKindRepository;

class ResourceKindDoctrineRepository extends EntityRepository implements ResourceKindRepository {
    public function save(ResourceKind $resourceKind): ResourceKind {
        $this->getEntityManager()->persist($resourceKind);
        return $resourceKind;
    }
}
