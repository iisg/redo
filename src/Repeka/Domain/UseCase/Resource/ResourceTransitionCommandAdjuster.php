<?php
namespace Repeka\Domain\UseCase\Resource;

use Assert\Assertion;
use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Metadata\MetadataValueAdjuster\ResourceContentsAdjuster;

class ResourceTransitionCommandAdjuster implements CommandAdjuster {

    /** @var ResourceContentsAdjuster */
    private $resourceContentsAdjuster;

    public function __construct(ResourceContentsAdjuster $resourceContentsAdjuster) {
        $this->resourceContentsAdjuster = $resourceContentsAdjuster;
    }

    /**
     * @param ResourceTransitionCommand $command
     * @return ResourceTransitionCommand
     */
    public function adjustCommand(Command $command): Command {
        $newContents = $this->resourceContentsAdjuster->adjust($command->getContents());
        $currentContents = $this->resourceContentsAdjuster->adjust($command->getResource()->getContents());
        $command->getResource()->updateContents($currentContents);
        $transition = $command->getTransition();
        $workflow = $command->getResource()->getKind()->getWorkflow();
        if (!$transition instanceof ResourceWorkflowTransition && $workflow !== null) {
            $resource = $command->getResource();
            $transition = $resource->getWorkflow()->getTransition($transition);
        } else {
            Assertion::isInstanceOf($transition, ResourceWorkflowTransition::class, 'Invalid transiton given.');
        }
        return new ResourceTransitionCommand($command->getResource(), $newContents, $transition, $command->getExecutor());
    }
}
