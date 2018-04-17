<?php
namespace Repeka\Domain\UseCase\Resource\AutoAssign;

use Repeka\Application\Cqrs\Event\CommandHandledEvent;
use Repeka\Domain\Cqrs\CommandBus;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\UseCase\Resource\ResourceTransitionCommand;
use Repeka\Domain\UseCase\Resource\ResourceUpdateContentsCommand;
use Repeka\Domain\Utils\EntityUtils;

class ResourceMetadataAutoAssignListener {

    /** @var CommandBus */
    private $commandBus;

    public function __construct(CommandBus $commandBus) {
        $this->commandBus = $commandBus;
    }

    public function onResourceTransition(CommandHandledEvent $event) {
        /** @var ResourceTransitionCommand $command */
        $command = $event->getCommand();
        $resource = $command->getResource();
        $executor = $command->getExecutor();
        $this->autoAssingMetadata($resource, $executor);
    }

    private function autoAssingMetadata(ResourceEntity $resource, ?User $executor = null): ResourceEntity {
        if (!$resource->getKind()->getWorkflow() || !$executor) {
            return $resource;
        }
        $resourceMetadata = $resource->getKind()->getMetadataList();
        $resourceMetadataIds = EntityUtils::mapToIds($resourceMetadata);
        $resourceContents = $resource->getContents();
        $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($resource);
        foreach ($autoAssignMetadataIds as $metadataId) {
            if (in_array($metadataId, $resourceMetadataIds)) {
                if (!in_array($executor->getId(), $resourceContents->getValues($metadataId))) {
                    $newResourceContents = $resourceContents->withMergedValues($metadataId, [$executor->getId()]);
                    $resource = $this->commandBus->handle(
                        new ResourceUpdateContentsCommand($resource, $newResourceContents, $executor)
                    );
                }
            }
        }
        return $resource;
    }

    private function getAutoAssignMetadataIds(ResourceEntity $resource): array {
        /** @var ResourceWorkflowPlace[] $places */
        $places = $resource->getWorkflow()->getPlaces($resource);
        $metadataIds = [];
        foreach ($places as $place) {
            $metadataIds = array_unique(array_merge($metadataIds, $place->restrictingMetadataIds()->autoAssign()->get()));
        }
        return $metadataIds;
    }
}
