<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommand;
use Repeka\Domain\Cqrs\AuditedCommand;
use Repeka\Domain\Cqrs\NonValidatedCommand;
use Repeka\Domain\Cqrs\RequireOperatorRole;
use Repeka\Domain\Factory\BulkChanges\BulkChange;

class ResourceAddPendingUpdateCommand extends AbstractCommand implements AuditedCommand, NonValidatedCommand {
    use RequireOperatorRole;

    private $pendingUpdate;
    private $listQuery;

    public function __construct(ResourceListQuery $listQuery, BulkChange $pendingUpdate) {
        $this->listQuery = $listQuery;
        $this->pendingUpdate = $pendingUpdate;
    }

    public function getPendingUpdate(): BulkChange {
        return $this->pendingUpdate;
    }

    public function getListQuery(): ResourceListQuery {
        return $this->listQuery;
    }

    public function getAuditData(): array {
        return ['update' => $this->pendingUpdate->toArray()];
    }
}
