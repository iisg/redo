<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Cqrs\Command;
use Repeka\Domain\Cqrs\CommandAdjuster;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Validation\Strippers\NonExistingMetadataStripper;
use Repeka\Domain\Validation\Strippers\UnknownLanguageStripper;

class ResourceWorkflowUpdateCommandAdjuster implements CommandAdjuster {
    /** @var UnknownLanguageStripper */
    protected $unknownLanguageStripper;
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
            $this->adjustWorkflowPlaces($command->getResourceClass(), $command->getPlaces()),
            $command->getTransitions(),
            $command->getDiagram(),
            $command->getThumbnail()
        );
    }

    protected function adjustWorkflowPlaces(string $resourceClass, array $workflowPlaces): array {
        $workflowPlaces = array_map(
            function ($workflowPlace) {
                $workflowPlaceArray = $workflowPlace instanceof ResourceWorkflowPlace
                    ? $workflowPlace->toArray()
                    : ResourceWorkflowPlace::fromArray($workflowPlace)->toArray(); // ensure we have all keys
                $workflowPlaceArray['label'] = $this->unknownLanguageStripper->removeUnknownLanguages($workflowPlaceArray['label']);
                return ResourceWorkflowPlace::fromArray($workflowPlaceArray);
            },
            $workflowPlaces
        );
        return $this->nonExistingMetadataStripper->removeNonExistingMetadata($workflowPlaces, $resourceClass);
    }
}
