<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceWorkflow;

interface ResourceWorkflowPlugins {
    /** @return ResourceWorkflowPlugin[] */
    public function getRegisteredPlugins(ResourceWorkflow $workflow): array;
}
