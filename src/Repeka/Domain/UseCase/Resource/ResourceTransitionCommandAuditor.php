<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Cqrs\AbstractCommandAuditor;
use Repeka\Domain\Cqrs\Command;

class ResourceTransitionCommandAuditor extends AbstractCommandAuditor {
    /**
     * @param ResourceTransitionCommand $command
     * @return array
     */
    public function beforeHandling(Command $command): ?array {
        $transition = $command->getTransition();
        $entryData = array_merge(
            $command->getResource()->getAuditData(),
            [
                'transitionId' => $transition->getId(),
                'transitionLabel' => $transition->getLabel(),
            ]
        );
        return $entryData;
    }
}
