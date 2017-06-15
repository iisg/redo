<?php
namespace Repeka\Domain\UseCase\Resource;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceAttachmentHelper;

class ResourceCreateCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceAttachmentHelper */
    private $attachmentHelper;

    public function __construct(ResourceRepository $resourceRepository, ResourceAttachmentHelper $attachmentHelper) {
        $this->resourceRepository = $resourceRepository;
        $this->attachmentHelper = $attachmentHelper;
    }

    public function handle(ResourceCreateCommand $command): ResourceEntity {
        $resource = new ResourceEntity($command->getKind(), $command->getContents());
        if ($resource->getWorkflow()) {
            $this->ensureCanEnterTheFirstWorkflowState($resource);
        }
        $resource = $this->resourceRepository->save($resource);
        Assertion::integer($resource->getId());
        $this->attachmentHelper->moveFilesToDestinationPaths($resource);
        return $resource;
    }

    private function ensureCanEnterTheFirstWorkflowState(ResourceEntity $resource): void {
        $helper = $resource->getWorkflow()->getTransitionHelper();
        $initialPlaceId = $resource->getWorkflow()->getPlaces()[0]->getId();
        if (!$helper->placeIsPermittedByResourceMetadata($initialPlaceId, $resource)) {
            throw new ResourceCannotEnterPlaceException($initialPlaceId, $resource);
        }
    }
}
