<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriverFactory;

class ResourceWorkflowSimulateCommandHandler {
    /** @var ResourceWorkflowDriverFactory */
    private $workflowDriverFactory;

    public function __construct(ResourceWorkflowDriverFactory $workflowDriverFactory) {
        $this->workflowDriverFactory = $workflowDriverFactory;
    }

    /** @return ResourceWorkflow[] */
    public function handle(ResourceWorkflowSimulateCommand $command): array {
        $simulatedWorkflow = new ResourceWorkflow([], $command->getPlaces(), $command->getTransitions(), 'books');
        $this->workflowDriverFactory->setForWorkflow($simulatedWorkflow);
        $tempResource = new ResourceWorkflowSimulationResource();
        $simulatedWorkflow->setCurrentPlaces($tempResource, $command->getCurrentPlaces());
        if ($command->getTransitionId()) {
            $simulatedWorkflow->apply($tempResource, $command->getTransitionId());
        }
        return [
            'places' => $simulatedWorkflow->getPlaces($tempResource),
            'transitions' => $simulatedWorkflow->getTransitions($tempResource),
        ];
    }
}
