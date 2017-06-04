<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Entity\ResourceEntity;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\User;
use Repeka\Domain\Exception\ResourceWorkflow\ResourceCannotEnterPlaceException;
use Repeka\Domain\Repository\ResourceRepository;

class ResourceTransitionCommandHandler {
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository) {
        $this->resourceRepository = $resourceRepository;
    }

    /** @return ResourceWorkflow[] */
    public function handle(ResourceTransitionCommand $command): ResourceEntity {
        $resource = $command->getResource();
        $this->ensureExecutorHasAppropriateRole($resource, $command->getTransitionId(), $command->getExecutor());
        $resource->applyTransition($command->getTransitionId());
        return $this->resourceRepository->save($resource);
    }

    private function ensureExecutorHasAppropriateRole(ResourceEntity $resource, string $transitionId, User $executor) {
        $helper = $resource->getWorkflow()->getTransitionHelper();
        if (!$helper->transitionIsPossible($transitionId, $resource, $executor)) {
            $transition = $resource->getWorkflow()->getTransition($transitionId);
            throw new ResourceCannotEnterPlaceException($transition->getToIds(), $resource);
        }
    }
}
