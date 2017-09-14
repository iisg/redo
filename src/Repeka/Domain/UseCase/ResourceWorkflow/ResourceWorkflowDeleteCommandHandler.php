<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowDeleteCommandHandler {
    /** @var ResourceWorkflowRepository */
    private $resourceWorkflowRepository;

    public function __construct(ResourceWorkflowRepository $resourceWorkflowRepository) {
        $this->resourceWorkflowRepository = $resourceWorkflowRepository;
    }

    public function handle(ResourceWorkflowDeleteCommand $command): void {
        $this->resourceWorkflowRepository->delete($command->getResourceWorkflow());
    }
}
