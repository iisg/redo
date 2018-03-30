<?php
namespace Repeka\Plugins\Ocr\Model;

use Repeka\Domain\Entity\MetadataControl;
use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class RepekaOcrResourceWorkflowPlugin extends ResourceWorkflowPlugin {
    /** @return ResourceWorkflowPluginConfigurationOption[] */
    public function getConfigurationOptions(): array {
        return [
            new ResourceWorkflowPluginConfigurationOption('metadataToOcr', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('metadataForResult', MetadataControl::TEXT()),
            new ResourceWorkflowPluginConfigurationOption('transitionAfterOcr', MetadataControl::TEXT()),
        ];
    }
}
