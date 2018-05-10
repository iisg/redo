<?php
namespace Repeka\Application\Workflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPlugins;

class RepekaResourceWorkflowPlugins implements ResourceWorkflowPlugins {
    private $plugins = [];

    public function __construct(iterable $plugins) {
        array_push($this->plugins, ...$plugins); // iterable to array, https://stackoverflow.com/a/44588822/878514
    }

    /** @return ResourceWorkflowPlugin[] */
    public function getRegisteredPlugins(ResourceWorkflow $workflow): array {
        return array_filter(
            $this->plugins,
            function (ResourceWorkflowPlugin $plugin) use ($workflow) {
                return $plugin->supports($workflow);
            }
        );
    }
}
