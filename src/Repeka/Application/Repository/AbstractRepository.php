<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityRepository;
use Repeka\Domain\Exception\EntityNotFoundException;

abstract class AbstractRepository extends EntityRepository {
    protected function persist($entity) {
        $this->getEntityManager()->persist($entity);
        return $entity;
    }

    protected function findById($id) {
        $entity = $this->find($id);
        if (!$entity) {
            throw new EntityNotFoundException($this, $id);
        }
        return $entity;
    }
}
