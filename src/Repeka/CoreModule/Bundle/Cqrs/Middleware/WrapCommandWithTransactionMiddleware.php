<?php
namespace Repeka\CoreModule\Bundle\Cqrs\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Repeka\CoreModule\Domain\Cqrs\Command;

/**
 * Almost the same as https://goo.gl/lXX1R0 but returns a result.
 */
class WrapCommandWithTransactionMiddleware implements CommandBusMiddleware {
    /** @var ManagerRegistry */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry) {
        $this->managerRegistry = $managerRegistry;
    }

    public function handle(Command $command, callable $next) {
        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManager($this->managerRegistry->getDefaultManagerName());
        try {
            return $entityManager->transactional(
                function () use ($command, $next) {
                    return $next($command);
                }
            );
        } catch (\Exception $exception) {
            $this->managerRegistry->resetManager($this->managerRegistry->getDefaultManagerName());
            throw $exception;
        }
    }
}
