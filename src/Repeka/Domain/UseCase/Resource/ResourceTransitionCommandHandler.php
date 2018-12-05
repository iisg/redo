<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceTransitionCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /** @return ResourceEntity|null */
    public function handle(ResourceTransitionCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $resource->updateContents($command->getContents());
        $transitionId = $command->getTransition()->getId();
        $resource = $this->resourceRepository->save($resource);
        if ($resource->hasWorkflow()) {
            $resource->applyTransition($transitionId);
        }
        return $resource;
    }
}
