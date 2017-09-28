<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowUsingMetadataAsAssigneeQueryHandler {
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;

    public function __construct(ResourceWorkflowRepository $workflowRepository) {
        $this->workflowRepository = $workflowRepository;
    }

    public function handle(ResourceWorkflowUsingMetadataAsAssigneeQuery $query) {
        return $this->workflowRepository->findByAssigneeMetadata($query->getMetadata());
    }
}
