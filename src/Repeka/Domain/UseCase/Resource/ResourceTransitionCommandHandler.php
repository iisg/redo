<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowTransition;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;

class ResourceTransitionCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFileHelper */
    private $fileHelper;

    public function __construct(ResourceRepository $resourceRepository, ResourceFileHelper $fileHelper) {
        $this->resourceRepository = $resourceRepository;
        $this->fileHelper = $fileHelper;
    }

    /** @return ResourceEntity|null */
    public function handle(ResourceTransitionCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $resource->updateContents($command->getContents());
        $transitionId = $command->getTransition()->getId();
        $resource = $this->resourceRepository->save($resource);
        $this->manageResourceFiles($resource);
        if ($resource->hasWorkflow()) {
            $resource->applyTransition($transitionId);
        }
        return $resource;
    }

    private function manageResourceFiles(ResourceEntity $resource): ResourceEntity {
        $this->fileHelper->prune($resource);
        $this->fileHelper->moveFilesToDestinationPaths($resource);
        return $resource;
    }
}
