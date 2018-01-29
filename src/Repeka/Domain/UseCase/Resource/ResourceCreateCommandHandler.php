<?php
namespace Repeka\Domain\UseCase\Resource;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;

class ResourceCreateCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFileHelper */
    private $fileHelper;

    public function __construct(ResourceRepository $resourceRepository, ResourceFileHelper $fileHelper) {
        $this->resourceRepository = $resourceRepository;
        $this->fileHelper = $fileHelper;
    }

    public function handle(ResourceCreateCommand $command): ResourceEntity {
        $resource = new ResourceEntity($command->getKind(), $command->getContents(), $command->getResourceClass());
        if ($resource->getWorkflow()) {
            $this->ensureCanEnterTheFirstWorkflowState($resource);
            $initialPlace = $resource->getWorkflow()->getInitialPlace();
            $resource->getWorkflow()->setCurrentPlaces($resource, [$initialPlace->getId()]);
        }
        $resource = $this->resourceRepository->save($resource);
        Assertion::integer($resource->getId());
        $this->fileHelper->moveFilesToDestinationPaths($resource);
        return $resource;
    }

    private function ensureCanEnterTheFirstWorkflowState(ResourceEntity $resource): void {
        $initialPlace = $resource->getWorkflow()->getInitialPlace();
        if (!$initialPlace->resourceHasRequiredMetadata($resource)) {
            throw new ResourceCannotEnterPlaceException($initialPlace->getId(), $resource);
        }
    }
}
