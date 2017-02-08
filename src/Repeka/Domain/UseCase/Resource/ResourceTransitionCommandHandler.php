<?php
namespace Repeka\Domain\UseCase\Resource;

use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceTransitionCommandHandler {
    /** @var ResourceWorkflowRepository */
    private $workflowRegistry;
    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(ResourceWorkflowRepository $workflowRepository, ResourceRepository $resourceRepository) {
        $this->workflowRegistry = $workflowRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function handle(ResourceTransitionCommand $command) {
        $workflow = $this->workflowRegistry->get($command->getResource());
        $resource = $workflow->apply($command->getResource(), $command->getTransition());
        return $this->resourceRepository->save($resource);
    }
}
