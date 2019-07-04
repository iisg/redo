<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Application\Entity\ResultSetMappings;
use Repeka\Application\Repository\Transactional;
use Repeka\Domain\Factory\ResourceListQuerySqlFactory;

class ResourceAddPendingUpdateCommandHandler {
    use Transactional;

    /** @var ResourceListQueryAdjuster */
    private $listQueryAdjuster;

    public function __construct(ResourceListQueryAdjuster $adjuster) {
        $this->listQueryAdjuster = $adjuster;
    }

    public function handle(ResourceAddPendingUpdateCommand $command) {
        return $this->transactional(
            function () use ($command) {
                $listQuery = $this->listQueryAdjuster->adjustCommand($command->getListQuery());
                $sqlFactory = new ResourceListQuerySqlFactory($listQuery);
                $where = $sqlFactory->getWhereClause();
                $from = implode(', ', $sqlFactory->getFroms());
                $change = json_encode($command->getPendingUpdate()->toArray());
                $query = "UPDATE resource SET pending_updates = match.pending_updates::jsonb || (:change)::jsonb FROM
                        (SELECT id, pending_updates FROM $from WHERE $where) AS match WHERE resource.id=match.id
                        RETURNING resource.id id";
                return $this->entityManager->createNativeQuery($query, ResultSetMappings::scalar('id'))
                    ->setParameters(array_merge($sqlFactory->getParams(), ['change' => $change]))
                    ->execute();
            }
        );
    }
}
