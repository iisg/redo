<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Utils\EntityUtils;

class ResourceWorkflowPlugins {
    /** @var iterable */
    private $plugins;
    /** @var ResourceWorkflowPlugin[] */
    private $pluginsArray;

    /** @param ResourceWorkflowPlugin[] $plugins */
    public function __construct(iterable $plugins) {
        $this->plugins = $plugins;
    }

    /**
     * Lazy building of this array is not a premature optimization.
     * Its main aim is to break CommandBus -> Some listener in plugin -> CommandBus circular dependencies.
     */
    private function pluginsArray(): array {
        if (!$this->pluginsArray) {
            $this->pluginsArray = [];
            foreach ($this->plugins as $plugin) {
                $this->pluginsArray[$plugin->getName()] = $plugin;
            }
        }
        return $this->pluginsArray;
    }

    /** @return ResourceWorkflowPlugin[] */
    public function getRegisteredPlugins(ResourceWorkflow $workflow): array {
        return array_filter(
            $this->pluginsArray(),
            function (ResourceWorkflowPlugin $plugin) use ($workflow) {
                return $plugin->supports($workflow);
            }
        );
    }

    public function getPlugin(ResourceWorkflowPlacePluginConfiguration $config): ResourceWorkflowPlugin {
        return $this->pluginsArray()[$config->getPluginName()];
    }

    /** @return ResourceWorkflowPlacePluginConfiguration[] */
    public function getPluginsConfig(array $placesOrIds, ResourceWorkflow $workflow): array {
        $configs = [];
        foreach ($placesOrIds as $placeOrId) {
            $place = $placeOrId instanceof ResourceWorkflowPlace
                ? $placeOrId
                : EntityUtils::getByIds([$placeOrId], $workflow->getPlaces())[0];
            $configs = array_merge($configs, $place->getPluginsConfig());
        }
        return $configs;
    }
}
