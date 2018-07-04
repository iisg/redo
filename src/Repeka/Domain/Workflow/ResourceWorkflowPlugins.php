<?php
namespace Repeka\Domain\Workflow;

use Repeka\Domain\Entity\ResourceWorkflow;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlace;
use Repeka\Domain\Entity\Workflow\ResourceWorkflowPlacePluginConfiguration;
use Repeka\Domain\Utils\EntityUtils;

class ResourceWorkflowPlugins {
    private $plugins = [];

    /** @param ResourceWorkflowPlugin[] $plugins */
    public function __construct(iterable $plugins) {
        foreach ($plugins as $plugin) {
            $this->plugins[$plugin->getName()] = $plugin;
        }
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

    public function getPlugin(ResourceWorkflowPlacePluginConfiguration $config): ResourceWorkflowPlugin {
        return $this->plugins[$config->getPluginName()];
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
