<?php
namespace Repeka\Domain\UseCase\ResourceManagement;

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
        if ($command->getResourceKind()) {
            EntityUtils::forceSetField($resource, $command->getResourceKind(), 'kind');
        }
        if ($resource->hasWorkflow()) {
            $resource->getWorkflow()->setCurrentPlaces($resource, $command->getPlacesIds());
        }
        $resource->updateContents($command->getContents());
        $resource = $this->resourceRepository->save($command->getResource());
        $this->manageResourceFiles($resource);
        return $resource;
    }

    private function manageResourceFiles(ResourceEntity $resource): ResourceEntity {
        $this->fileHelper->prune($resource);
        $this->fileHelper->moveFilesToDestinationPaths($resource);
        return $resource;
    }
}
