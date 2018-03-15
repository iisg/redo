<?php
namespace Repeka\Domain\UseCase\Resource;

use Assert\Assertion;
use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Upload\ResourceFileHelper;

class ResourceCreateCommandHandler extends ResourceTransitionCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;
    /** @var ResourceFileHelper */
    private $fileHelper;

    public function __construct(
        ResourceRepository $resourceRepository,
        ResourceFileHelper $fileHelper,
        ResourceUpdateContentsCommandHandler $resourceUpdateContentsCommandHandler
    ) {
        parent::__construct($resourceRepository, $resourceUpdateContentsCommandHandler);
        $this->resourceRepository = $resourceRepository;
        $this->fileHelper = $fileHelper;
    }

    /**
     * @param ResourceCreateCommand $command
     * @return ResourceEntity
     */
    public function handle($command): ResourceEntity {
        $resource = new ResourceEntity($command->getKind(), $command->getContents());
        $workflow = $resource->getWorkflow();
        if ($workflow) {
            $this->ensureCanEnterTheFirstWorkflowState($resource);
            $initialPlace = $workflow->getInitialPlace();
            $workflow->setCurrentPlaces($resource, [$initialPlace->getId()]);
        }
        $resource = $this->resourceRepository->save($resource);
        Assertion::integer($resource->getId());
        $this->fileHelper->moveFilesToDestinationPaths($resource);
        return $workflow ? $this->autoAssingMetadata($resource, $command->getExecutor()) : $resource;
    }

    private function ensureCanEnterTheFirstWorkflowState(ResourceEntity $resource): void {
        $initialPlace = $resource->getWorkflow()->getInitialPlace();
        if (!$initialPlace->resourceHasRequiredMetadata($resource)) {
            throw new ResourceCannotEnterPlaceException($initialPlace->getId(), $resource);
        }
    }
}
