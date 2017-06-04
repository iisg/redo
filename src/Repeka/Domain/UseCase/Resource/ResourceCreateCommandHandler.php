<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceCreateCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceCreateCommand $command): ResourceEntity {
        $resource = new ResourceEntity($command->getKind(), $command->getContents());
        if ($resource->getWorkflow()) {
            $this->ensureCanEnterTheFirstWorkflowState($resource);
        }
        return $this->resourceRepository->save($resource);
    }

    private function ensureCanEnterTheFirstWorkflowState(ResourceEntity $resource):void {
        $helper = $resource->getWorkflow()->getTransitionHelper();
        $initialPlaceId = $resource->getWorkflow()->getPlaces()[0]->getId();
        if (!$helper->placeIsPermittedByResourceMetadata($initialPlaceId, $resource)) {
            throw new ResourceCannotEnterPlaceException($initialPlaceId, $resource);
        }
    }
}
