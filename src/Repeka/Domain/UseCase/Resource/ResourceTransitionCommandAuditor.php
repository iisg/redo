<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Exception\ResourceWorkflow\NoSuchTransitionException;

class ResourceTransitionCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceTransitionCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): ?array {
        $entryData = array_merge($command->getResource()->getAuditData(), [
            'transitionId' => $command->getTransitionId(),
        ]);
        try {
            $transition = $command->getResource()->getWorkflow()->getTransition($command->getTransitionId());
            $entryData = array_merge($entryData, [
                'transitionLabel' => $transition->getLabel(),
            ]);
        } catch (NoSuchTransitionException $e) {
        }
        return $entryData;
    }
}
