<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceUpdateContentsCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceUpdateContentsCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): array {
        return ['before' => $command->getResource()->getAuditData()];
    }

    /**
     * @param ResourceUpdateContentsCommand $command
     * @param ResourceEntity $updatedResource
     * @return array
     */
    public function afterHandling(Command $command, $updatedResource, ?array $beforeHandlingResult): array {
        return array_merge(
            $beforeHandlingResult,
            ['after' => $updatedResource->getAuditData()]
        );
    }

    public function doSaveBeforeHandlingResult(): bool {
        return false;
    }
}
