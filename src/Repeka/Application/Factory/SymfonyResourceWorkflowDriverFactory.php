<?php
namespace Repeka\Application\Factory;

use Repeka\Application\Workflow\SymfonyResourceWorkflowDriver;
use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowDriverFactory;
use Repeka\Domain\Workflow\ResourceWorkflowDriver;

class SymfonyResourceWorkflowDriverFactory extends ResourceWorkflowDriverFactory {
    protected function createDriver(ResourceWorkflow $resourceWorkflow): ResourceWorkflowDriver {
        return new SymfonyResourceWorkflowDriver($resourceWorkflow);
    }
}
