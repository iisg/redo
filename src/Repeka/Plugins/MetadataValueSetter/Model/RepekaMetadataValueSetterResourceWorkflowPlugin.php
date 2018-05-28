<?php
namespace Repeka\Plugins\MetadataValueSetter\Model;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaMetadataValueSetterResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    /** @return ResourceWorkflowPluginConfigurationOption[] */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('metadataName', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('metadataValue', MetadataControl::TEXT()),
        ];
    }
}
