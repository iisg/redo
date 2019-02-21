<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriverFactory;
use Repeka\Domain\Repository\ResourceWorkflowRepository;

class ResourceWorkflowCreateCommandHandler {
    /** @var ResourceWorkflowRepository */
    private $workflowRepository;
    /** @var ResourceWorkflowDriverFactory */
    private $driverFactory;

    public function __construct(ResourceWorkflowRepository $workflowRepository, ResourceWorkflowDriverFactory $driverFactory) {
        $this->workflowRepository = $workflowRepository;
        $this->driverFactory = $driverFactory;
    }

    public function handle(ResourceWorkflowCreateCommand $command): ResourceWorkflow {
        $workflow = new ResourceWorkflow(
            $command->getName(),
            $command->getPlaces(),
            $command->getTransitions(),
            $command->getResourceClass(),
            $command->getDiagram(),
            $command->getThumbnail()
        );
        $workflow = $this->workflowRepository->save($workflow);
        $this->driverFactory->setForWorkflow($workflow);
        return $workflow;
    }
}
