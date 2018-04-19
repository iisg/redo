<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Utils\EntityUtils;

class ResourceTransitionCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceUpdateContentsCommandHandler */
    private $resourceUpdateContentsCommandHandler;

    public function __construct(
        ResourceRepository $resourceRepository,
        ResourceUpdateContentsCommandHandler $resourceUpdateContentsCommandHandler
    ) {
        $this->resourceRepository = $resourceRepository;
        $this->resourceUpdateContentsCommandHandler = $resourceUpdateContentsCommandHandler;
    }

    /**
     * @param ResourceTransitionCommand $command
     * @return ResourceEntity
     */
    public function handle($command): ResourceEntity {
        $resource = $command->getResource();
        $resource->applyTransition($command->getTransitionId());
        $resource = $this->resourceRepository->save($resource);
        return $this->autoAssingMetadata($resource, $command->getExecutor());
    }

    public function autoAssingMetadata(ResourceEntity $resource, User $executor): ResourceEntity {
        if (!$resource->getKind()->getWorkflow()) {
            return $resource;
        }
        $resourceMetadata = $resource->getKind()->getMetadataList();
        $resourceMetadataIds = EntityUtils::mapToIds($resourceMetadata);
        $resourceContents = $resource->getContents();
        $autoAssignMetadataIds = $this->getAutoAssignMetadataIds($resource);
        foreach ($autoAssignMetadataIds as $metadataId) {
            if (in_array($metadataId, $resourceMetadataIds)) {
                if (!in_array($metadataId, $resourceContents->getValues($metadataId))) {
                    $newResourceContents = $resourceContents->withMergedValues($metadataId, [$executor->getId()]);
                    $resource = $this->resourceUpdateContentsCommandHandler->handle(
                        new ResourceUpdateContentsCommand($resource, $newResourceContents)
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
