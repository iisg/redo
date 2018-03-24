<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class ResourceWorkflowUpdateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    private $unknownLanguageStripper;

    public function __construct(UnknownLanguageStripper $unknownLanguageStripper) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
    }

    /** @param ResourceWorkflowUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new ResourceWorkflowUpdateCommand(
            $command->getWorkflow(),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getName()),
            $command->getPlaces(),
            $command->getTransitions(),
            $command->getDiagram(),
            $command->getThumbnail()
        );
    }
}
