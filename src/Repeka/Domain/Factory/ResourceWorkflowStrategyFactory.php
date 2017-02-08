<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;

abstract class ResourceWorkflowStrategyFactory {
    public function setForWorkflow(ResourceWorkflow $resourceWorkflow): ResourceWorkflow {
        $strategy = $this->createStrategy($resourceWorkflow);
        $resourceWorkflow->setWorkflowStrategy($strategy);
        return $resourceWorkflow;
    }

    abstract protected function createStrategy(ResourceWorkflow $resourceWorkflow): ResourceWorkflowStrategy;
}
