<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class ResourceDeleteCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceDeleteCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): array {
        return ['before' => $command->getResource()->getAuditData()];
    }
}
