<?php
namespace Repeka\Domain\UseCase\ResourceWorkflow;

use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPlugins;

class ResourceWorkflowPluginsQueryHandler {
    /** @var ResourceWorkflowPlugins */
    private $resourceWorkflowPlugins;

    public function __construct(ResourceWorkflowPlugins $resourceWorkflowPlugins) {
        $this->resourceWorkflowPlugins = $resourceWorkflowPlugins;
    }

    /** @return ResourceWorkflowPlugin[] */
    public function handle(ResourceWorkflowPluginsQuery $query): array {
        return $this->resourceWorkflowPlugins->getRegisteredPlugins($query->getWorkflow());
    }
}
