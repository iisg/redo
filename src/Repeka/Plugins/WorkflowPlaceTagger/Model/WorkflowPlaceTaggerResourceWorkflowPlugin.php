<?php
namespace Repeka\Plugins\WorkflowPlaceTagger\Model;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class WorkflowPlaceTaggerResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('tagName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('tagValue', MetadataControl::TEXT()),
        ];
    }
}
