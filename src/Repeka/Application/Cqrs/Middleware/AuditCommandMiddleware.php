<?php
namespace Repeka\Application\Cqrs\Middleware;

use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAuditor;
use Repeka\Domain\Factory\Audit;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AuditCommandMiddleware implements CommandBusMiddleware {
    private $container;

    /** @var Audit */
    private $audit;

    public function __construct(ContainerInterface $container, Audit $audit) {
        $this->container = $container;
        $this->audit = $audit;
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
                    $afterEntry = $auditor->afterHandling($command, $result, $beforeEntry);
                    $this->auditSuccess($command, $beforeEntry, $afterEntry, $auditor->doSaveBeforeHandlingResult());
                    return $result;
                } catch (\Exception $e) {
                    $errorEntry = $auditor->afterError($command, $e, $beforeEntry);
                    $this->auditFailure($command, $beforeEntry, $errorEntry, $auditor->doSaveBeforeHandlingResult());
                    throw $e;
                }
            } else {
                throw new \InvalidArgumentException(
                    "Could not find an auditor for the {$command->getCommandName()}. "
                    . "Looking for the {$auditorId}. "
                    . "If the command is not meant to have an auditor, remove the AuditedCommand interface from it."
                );
            }
        } else {
            return $next($command);
        }
    }

    private function getAuditorId(Command $command) {
        return $command->getCommandClassName() . 'Auditor';
    }

    private function auditSuccess(Command $command, ?array $beforeEntry, ?array $afterEntry, bool $saveBeforeEntry) {
        $this->saveAuditEntries($command, $saveBeforeEntry ? $beforeEntry : null, $afterEntry, true);
    }

    private function auditFailure(Command $command, ?array $beforeEntry, ?array $afterEntry, bool $saveBeforeEntry) {
        $this->saveAuditEntries($command, $saveBeforeEntry ? $beforeEntry : null, $afterEntry, false);
    }

    private function saveAuditEntries(Command $command, ?array $beforeEntry, ?array $afterEntry, bool $successful) {
        foreach ([$beforeEntry, $afterEntry] as $entryData) {
            if (is_array($entryData)) {
                $this->audit->newEntry($command->getCommandName(), $command->getExecutor(), $entryData, $successful);
            }
        }
    }
}
