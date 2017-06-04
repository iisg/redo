<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceUpdateContentsCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceUpdateContentsCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $resource->updateContents($command->getContents());
        if ($resource->getWorkflow()) {
            $this->ensureStillValidInPlace($resource);
        }
        return $this->resourceRepository->save($resource);
    }

    private function ensureStillValidInPlace(ResourceEntity $resource): void {
        $helper = $resource->getWorkflow()->getTransitionHelper();
        $currentPlaces = $resource->getWorkflow()->getPlaces($resource);
        foreach ($currentPlaces as $currentPlace) {
            if (!$helper->placeIsPermittedByResourceMetadata($currentPlace->getId(), $resource)) {
                throw new ResourceCannotEnterPlaceException($currentPlace->getId(), $resource);
            }
        }
    }
}
