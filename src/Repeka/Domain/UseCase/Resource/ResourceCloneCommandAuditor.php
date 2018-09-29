<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceCloneCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceCloneCommand $command
     * @param ResourceEntity $createdResource
     * @return array
     */
    public function afterHandling(Command $command, $createdResource, ?array $beforeHandlingResult): ?array {
        return ['after' => $createdResource->getAuditData()];
    }
}
