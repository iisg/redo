<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;

class ResourceWorkflowCreateCommandAdjuster extends ResourceWorkflowUpdateCommandAdjuster implements CommandAdjuster {
    /** @param ResourceWorkflowUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new ResourceWorkflowCreateCommand(
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getName()),
            $this->adjustWorkflowPlaces($command->getResourceClass(), $command->getPlaces()),
            $command->getTransitions(),
            $command->getResourceClass(),
            $command->getDiagram(),
            $command->getThumbnail()
        );
    }
}
