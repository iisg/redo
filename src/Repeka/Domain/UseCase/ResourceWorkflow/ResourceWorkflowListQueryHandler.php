<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowListQueryHandler {
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;

    public function __construct(ResourceWorkflowRepository $workflowRepository) {
        $this->workflowRepository = $workflowRepository;
    }

    /** @return ResourceWorkflow[] */
    public function handle(): array {
        return $this->workflowRepository->findAll();
    }
}
