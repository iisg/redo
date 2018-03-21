<?php
namespace Repeka\Application\Cqrs\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use Repeka\Application\Entity\UserEntity;
use Repeka\Application\Service\CurrentUserAware;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAuditor;
use Repeka\Domain\Entity\AuditEntry;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuditCommandMiddleware implements CommandBusMiddleware {
    use CurrentUserAware;

    private $container;

    /** @var ManagerRegistry */
    private $managerRegistry;

    public function __construct(ContainerInterface $container, ManagerRegistry $managerRegistry) {
        $this->container = $container;
        $this->managerRegistry = $managerRegistry;
    }

    public function handle(Command $command, callable $next) {
        if ($command instanceof AuditedCommand) {
            $auditorId = $this->getAuditorId($command);
            if ($this->container->has($auditorId)) {
                /** @var CommandAuditor $auditor */
                $auditor = $this->container->get($auditorId);
                $beforeEntry = $auditor->beforeHandling($command);
                try {
                    $result = $next($command);
                    $this->auditSuccess($command, $beforeEntry, $auditor->afterHandling($command, $result));
                    return $result;
                } catch (\Exception $e) {
                    $this->auditFailure($command, $beforeEntry, $auditor->afterError($command, $e));
                    throw $e;
                }
            } else {
                throw new \InvalidArgumentException("Could not find an auditor for the {$command->getCommandName()}. "
                    . "Looking for the {$auditorId}. "
                    . "If the command is not meant to have an auditor, remove the AuditedCommand interface from it.");
            }
        } else {
            return $next($command);
        }
    }

    private function getAuditorId(Command $command) {
        return get_class($command) . 'Auditor';
    }

    private function auditSuccess(Command $command, ?array $beforeEntry, ?array $afterEntry) {
        $this->saveAuditEntries($command, $beforeEntry, $afterEntry, true);
    }

    private function auditFailure(Command $command, ?array $beforeEntry, ?array $afterEntry) {
        $this->saveAuditEntries($command, $beforeEntry, $afterEntry, false);
    }

    private function saveAuditEntries(Command $command, ?array $beforeEntry, ?array $afterEntry, bool $successful) {
        $entityManager = $this->managerRegistry->getManager();
        $user = $this->getCurrentUser();
        if ($user) {
            // fetch the user again so it is managed in the current EntityManager
            // previous EM could have been closed and reset if the command has failed
            $user = $entityManager->find(UserEntity::class, $user->getId());
        }
        foreach ([$beforeEntry, $afterEntry] as $entryData) {
            if (is_array($entryData)) {
                $entry = new AuditEntry($command->getCommandName(), $user, $entryData, $successful);
                $entityManager->persist($entry);
            }
        }
        $entityManager->flush();
    }
}
