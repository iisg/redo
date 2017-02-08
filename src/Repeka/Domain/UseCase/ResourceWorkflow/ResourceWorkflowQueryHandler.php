<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceRepository;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowQueryHandler {
    /** @var ResourceRepository */
    private $workflowRepository;

    public function __construct(ResourceWorkflowRepository $workflowRepository) {
        $this->workflowRepository = $workflowRepository;
    }

    public function handle(ResourceWorkflowQuery $query): ResourceWorkflow {
        return $this->workflowRepository->findOne($query->getId());
    }
}
