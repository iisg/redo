<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;

class ResourceUpdateContentsCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFileHelper */
    private $fileHelper;

    public function __construct(ResourceRepository $resourceRepository, ResourceFileHelper $fileHelper) {
        $this->resourceRepository = $resourceRepository;
        $this->fileHelper = $fileHelper;
    }

    public function handle(ResourceUpdateContentsCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $resource->updateContents($command->getContents());
        if ($resource->getWorkflow()) {
            $this->ensureStillValidInPlace($resource);
        }
        $this->fileHelper->moveFilesToDestinationPaths($resource);
        return $this->resourceRepository->save($resource);
    }

    private function ensureStillValidInPlace(ResourceEntity $resource): void {
        $currentPlaces = $resource->getWorkflow()->getPlaces($resource);
        foreach ($currentPlaces as $currentPlace) {
            if (!$currentPlace->resourceHasRequiredMetadata($resource)) {
                throw new ResourceCannotEnterPlaceException($currentPlace->getId(), $resource);
            }
        }
    }
}
