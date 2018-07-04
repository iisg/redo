<?php
namespace Repeka\Domain\Entity\Workflow;

use Repeka\Domain\Workflow\ResourceWorkflowPlugin;

class ResourceWorkflowPlacePluginConfiguration {
    private $name;
    private $config;

    public function __construct(array $pluginConfig) {
        $this->name = $pluginConfig['name'];
        $this->config = $pluginConfig['config'];
    }

    public function getPluginName(): string {
        return $this->name;
    }

    public function isForPlugin(string $pluginClassName) {
        return $this->getPluginName() == ResourceWorkflowPlugin::getNameFromClassName($pluginClassName);
    }

    public function getConfigValue(string $optionName) {
        return $this->config[$optionName] ?? '';
    }

    public function toArray() {
        return [
            'name' => $this->name,
            'config' => $this->config,
        ];
    }
}
