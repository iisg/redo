<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class ResourceUpdateContentsCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceUpdateContentsCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): ?array {
        return $command->getResource()->getAuditData();
    }
}
