<?php
namespace Repeka\Application\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriver;
use Repeka\Domain\Factory\ResourceWorkflowDriverFactory;

class SymfonyResourceWorkflowDriverFactory extends ResourceWorkflowDriverFactory {
    protected function createDriver(ResourceWorkflow $resourceWorkflow): ResourceWorkflowDriver {
        return new SymfonyResourceWorkflowDriver($resourceWorkflow);
    }
}
