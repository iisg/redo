<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;
use Repeka\Domain\Utils\EntityUtils;

class ResourceGodUpdateCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFileHelper */
    private $fileHelper;

    public function __construct(ResourceRepository $resourceRepository, ResourceFileHelper $fileHelper) {
        $this->resourceRepository = $resourceRepository;
        $this->fileHelper = $fileHelper;
    }

    public function handle(ResourceGodUpdateCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $originalWorkflow = $resource->getWorkflow();
        if ($command->getResourceKind()) {
            EntityUtils::forceSetField($resource, $command->getResourceKind(), 'kind');
            EntityUtils::forceSetField($resource, $command->getResourceKind()->getResourceClass(), 'resourceClass');
        }
        if ($resource->hasWorkflow()) {
            if ($resource->getWorkflow() == $originalWorkflow) {
                if ($command->getPlacesIds()) {
                    $resource->getWorkflow()->setCurrentPlaces($resource, $command->getPlacesIds());
                }
            } else {
                $resource->getWorkflow()->setCurrentPlaces($resource, [$resource->getWorkflow()->getInitialPlace()->getId()]);
            }
        }
        $resource->updateContents($command->getContents());
        $resource = $this->resourceRepository->save($resource);
        $this->fileHelper->prune($resource);
        $this->fileHelper->moveFilesToDestinationPaths($resource);
        return $resource;
    }
}
