<?php
namespace Repeka\Application\Cqrs\Middleware;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;
use Repeka\Domain\Cqrs\Command;

/**
 * Almost the same as https://goo.gl/lXX1R0 but returns a result and does not use a transactional method of EntityManager that incorrectly
 * handles negative results.
 * @see https://github.com/doctrine/doctrine2/issues/3531
 */
class WrapCommandWithTransactionMiddleware implements CommandBusMiddleware {
    /** @var ManagerRegistry */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry) {
        $this->managerRegistry = $managerRegistry;
    }

    public function handle(Command $command, callable $next) {
        /** @var EntityManager $entityManager */
        $entityManager = $this->managerRegistry->getManager();
        $entityManager->getConnection()->beginTransaction();
        try {
            $result = $next($command);
            $entityManager->flush();
            $entityManager->getConnection()->commit();
            return $result;
        } catch (\Exception $exception) {
            $entityManager->close();
            $entityManager->getConnection()->rollBack();
            $this->managerRegistry->resetManager();
            throw $exception;
        }
    }
}
