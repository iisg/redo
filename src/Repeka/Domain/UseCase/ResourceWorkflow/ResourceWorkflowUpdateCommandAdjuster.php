<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Validation\Strippers\NonExistingMetadataStripper;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class ResourceWorkflowUpdateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    private $unknownLanguageStripper;
    /** @var NonExistingMetadataStripper */
    private $nonExistingMetadataStripper;

    public function __construct(
        UnknownLanguageStripper $unknownLanguageStripper,
        NonExistingMetadataStripper $nonExistingMetadataStripper
    ) {
        $this->unknownLanguageStripper = $unknownLanguageStripper;
        $this->nonExistingMetadataStripper = $nonExistingMetadataStripper;
    }

    /** @param ResourceWorkflowUpdateCommand $command */
    public function adjustCommand(Command $command): Command {
        return new ResourceWorkflowUpdateCommand(
            $command->getWorkflow(),
            $this->unknownLanguageStripper->removeUnknownLanguages($command->getName()),
            $this->nonExistingMetadataStripper->removeNonExistingMetadata($command->getPlaces(), $command->getResourceClass()),
            $command->getTransitions(),
            $command->getDiagram(),
            $command->getThumbnail()
        );
    }
}
