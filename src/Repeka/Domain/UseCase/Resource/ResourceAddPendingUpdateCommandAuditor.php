<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class ResourceAddPendingUpdateCommandAuditor extends AbstractCommandAuditor {

    /** @param ResourceAddPendingUpdateCommand $command */
    public function afterHandling(Command $command, $result, ?array $beforeHandlingResult): ?array {
        return array_merge(['totalCount' => count($result)], $command->getAuditData());
    }

    /** @param ResourceAddPendingUpdateCommand $command */
    public function afterError(Command $command, \Exception $exception, ?array $beforeHandlingResult): ?array {
        return array_merge(['errorMessage' => $exception->getMessage()], $command->getAuditData());
    }
}
