<?php
namespace Repeka\Application\Factory;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Factory\ResourceWorkflowStrategy;
use Repeka\Domain\Factory\ResourceWorkflowStrategyFactory;

class ResourceSymfonyWorkflowStrategyFactory extends ResourceWorkflowStrategyFactory {
    protected function createStrategy(ResourceWorkflow $resourceWorkflow): ResourceWorkflowStrategy {
        return new ResourceSymfonyWorkflowStrategy($resourceWorkflow);
    }
}
