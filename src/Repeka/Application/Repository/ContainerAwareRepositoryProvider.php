<?php
namespace Repeka\Application\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Repeka\Domain\Repository\RepositoryProvider;

class ContainerAwareRepositoryProvider implements RepositoryProvider {
    /** @var EntityManagerInterface */
    private $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function getForEntityType(string $entityType) {
        return $this->em->getRepository($entityType);
    }
}
