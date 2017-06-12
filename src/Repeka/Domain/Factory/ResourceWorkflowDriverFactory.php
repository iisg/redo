<?php
namespace Repeka\Domain\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;

abstract class ResourceWorkflowDriverFactory {
    public function setForWorkflow(ResourceWorkflow $resourceWorkflow): ResourceWorkflow {
        $driver = $this->createDriver($resourceWorkflow);
        $resourceWorkflow->setWorkflowDriver($driver);
        return $resourceWorkflow;
    }

    abstract protected function createDriver(ResourceWorkflow $resourceWorkflow): ResourceWorkflowDriver;
}
