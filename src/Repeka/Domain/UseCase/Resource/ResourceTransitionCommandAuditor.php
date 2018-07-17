<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Constants\SystemTransition;
use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Entity\ResourceEntity;

class ResourceTransitionCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceTransitionCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): ?array {
        return ['before' => $command->getResource()->getAuditData()];
    }

    /**
     * @param ResourceTransitionCommand $command
     * @param ResourceEntity $updatedResource
     * @return array
     */
    public function afterHandling(Command $command, $updatedResource, ?array $beforeHandlingResult): ?array {
        $transition = $command->getTransition();
        if (SystemTransition::isValid($transition->getId())) {
            return null;
        }
        return array_merge(
            $beforeHandlingResult,
            [
                'after' => $updatedResource->getAuditData(),
                'transitionId' => $transition->getId(),
                'transitionLabel' => $transition->getLabel(),
            ]
        );
    }

    public function doSaveBeforeHandlingResult(): bool {
        return false;
    }
}
