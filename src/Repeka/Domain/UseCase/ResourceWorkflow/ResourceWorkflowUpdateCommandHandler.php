<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowUpdateCommandHandler {
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;

    public function __construct(ResourceWorkflowRepository $workflowRepository) {
        $this->workflowRepository = $workflowRepository;
    }

    /** @return ResourceWorkflow[] */
    public function handle(ResourceWorkflowUpdateCommand $command): ResourceWorkflow {
        $workflow = $command->getWorkflow();
        $workflow->update(
            $command->getName(),
            $command->getPlaces(),
            $command->getTransitions(),
            $command->getDiagram(),
            $command->getThumbnail()
        );
        return $this->workflowRepository->save($workflow);
    }
}
