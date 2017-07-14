<?php

namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityManagerInterface;

trait Transactional {
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @required */
    public function setEntityManager(EntityManagerInterface $entityManager): void {
        $this->entityManager = $entityManager;
    }

    public function transactional(callable $function) {
        return $this->entityManager->transactional($function);
    }
}
