<?php
namespace Repeka\Domain\UseCase\Resource\AutoAssign;

use Repeka\Application\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Utils\EntityUtils;

class ResourceMetadataAutoAssignListener {

    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function onResourceTransition(BeforeCommandHandlingEvent $event) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $executor = $command->getExecutor();
        if ($resource->hasWorkflow() && $executor) {
            $executorResourceId = $executor->getUserData()->getId();
            $resourceMetadata = $resource->getKind()->getMetadataList();
            $resourceMetadataIds = EntityUtils::mapToIds($resourceMetadata);
            $resourceContents = $command->getContents();
            $targetPlaces = EntityUtils::filterByIds($command->getTransition()->getToIds(), $resource->getWorkflow()->getPlaces());
            $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($targetPlaces);
            foreach ($autoAssignMetadataIds as $metadataId) {
                if (in_array($metadataId, $resourceMetadataIds)) {
                    if (!in_array($executorResourceId, $resourceContents->getValues($metadataId))) {
                        $resourceContents = $resourceContents->withMergedValues($metadataId, [$executorResourceId]);
                    }
                }
            }
            $event->replaceCommand(new ResourceTransitionCommand($resource, $resourceContents, $command->getTransition(), $executor));
        }
    }

    /**
     * @param ResourceWorkflowPlace[] $places
     * @return int[]
     */
    private function getAutoAssignMetadataIds(array $places): array {
        $metadataIds = [];
        foreach ($places as $place) {
            $metadataIds = array_unique(array_merge($metadataIds, $place->restrictingMetadataIds()->autoAssign()->get()));
        }
        return $metadataIds;
    }
}
