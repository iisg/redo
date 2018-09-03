<?php
namespace Repeka\Domain\EventListener;

use Repeka\Domain\Cqrs\Event\BeforeCommandHandlingEvent;
use Repeka\Domain\Cqrs\Event\CommandEventsListener;
use Repeka\Domain\Entity\ResourceKind;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\Utils\EntityUtils;

class ResourceMetadataAutoAssignListener extends CommandEventsListener {
    public function onBeforeCommandHandling(BeforeCommandHandlingEvent $event): void {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $executor = $command->getExecutor();
        if ($resource->hasWorkflow() && $executor) {
            $executorResourceId = $executor->getUserData()->getId();
            $resourceContents = $command->getContents();
            $targetPlaces = EntityUtils::filterByIds($command->getTransition()->getToIds(), $resource->getWorkflow()->getPlaces());
            $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($targetPlaces, $resource->getKind());
            foreach ($autoAssignMetadataIds as $metadataId) {
                if (!in_array($executorResourceId, $resourceContents->getValuesWithoutSubmetadata($metadataId))) {
                    $resourceContents = $resourceContents->withMergedValues($metadataId, [$executorResourceId]);
                }
            }
            $event->replaceCommand(new ResourceTransitionCommand($resource, $resourceContents, $command->getTransition(), $executor));
        }
    }

    /**
     * @param ResourceWorkflowPlace[] $places
     * @return int[]
     */
    private function getAutoAssignMetadataIds(array $places, ResourceKind $resourceKind): array {
        $metadataIds = [];
        foreach ($places as $place) {
            $metadataIds = array_unique(
                array_merge($metadataIds, $place->restrictingMetadataIds()->autoAssign()->existingInResourceKind($resourceKind)->get())
            );
        }
        return $metadataIds;
    }
}
