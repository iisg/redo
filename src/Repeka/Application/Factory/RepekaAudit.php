<?php
namespace Repeka\Application\Factory;

use Doctrine\Common\Persistence\ManagerRegistry;
use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Entity\AuditEntry;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Factory\Audit;

class RepekaAudit implements Audit {
    use CurrentUserAware;

    /** @var ManagerRegistry */
    private $managerRegistry;

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function __construct(ManagerRegistry $managerRegistry) {
        $this->managerRegistry = $managerRegistry;
    }

    /** @SuppressWarnings("PHPMD.BooleanArgumentFlag") */
    public function newEntry(string $type, ?User $user, array $data = [], bool $successful = true): void {
        $entityManager = $this->managerRegistry->getManager();
        $user = $user ?: $this->getCurrentUser();
        if ($user) {
            // fetch the user again so it is managed in the current EntityManager
            // previous EM could have been closed and reset if the command has failed
            $user = $entityManager->find(UserEntity::class, $user->getId());
        }
        $entry = new AuditEntry($type, $user, $data, $successful);
        $entityManager->persist($entry);
        $entityManager->flush();
    }
}
