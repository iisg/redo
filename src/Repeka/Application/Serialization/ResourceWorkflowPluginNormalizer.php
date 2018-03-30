<?php
namespace Repeka\Application\Serialization;

use Repeka\Domain\Workflow\ResourceWorkflowPlugin;
use Repeka\Domain\Workflow\ResourceWorkflowPluginConfigurationOption;

class ResourceWorkflowPluginNormalizer extends AbstractNormalizer {
    /**
     * @param $resourceWorkflowPlugin ResourceWorkflowPlugin
     * @inheritdoc
     */
    public function normalize($resourceWorkflowPlugin, $format = null, array $context = []) {
        return [
            'name' => $resourceWorkflowPlugin->getName(),
            'configurationOptions' => $this->emptyArrayAsObject(array_map(function (ResourceWorkflowPluginConfigurationOption $option) {
                return [
                    'name' => $option->getName(),
                    'control' => $option->getControl()->getValue(),
                ];
            }, $resourceWorkflowPlugin->getConfigurationOptions())),
        ];
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null) {
        return $data instanceof ResourceWorkflowPlugin;
    }
}
