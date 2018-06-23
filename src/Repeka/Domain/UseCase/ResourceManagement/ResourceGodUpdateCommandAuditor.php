<?php
namespace Repeka\Domain\UseCase\ResourceManagement;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class ResourceGodUpdateCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceGodUpdateCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): ?array {
        return $command->getResource()->getAuditData();
    }
}
