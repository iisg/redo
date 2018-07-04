<?php declare(strict_types=1);
namespace Repeka\Migrations;

/**
 * Allow multiple resource workflow plugin configurations.
 * Before: pluginsConfig: {plugin1Name: plugin1Config, plugin2Name, plugin2Config}
 * After: pluginsConfig: [{name: plugin1Name, config: plugin1Config}, {name: plugin2Name, config: plugin2Config}]
 */
class Version20180705073559 extends RepekaMigration {
    public function migrate() {
        $workflows = $this->fetchAll('SELECT id, places FROM workflow');
        foreach ($workflows as $workflow) {
            $workflow['places'] = json_decode($workflow['places'], true);
            foreach ($workflow['places'] as &$place) {
                if (isset($place['pluginsConfig'])) {
                    $newPluginsConfig = [];
                    foreach ($place['pluginsConfig'] as $pluginName => $pluginConfig) {
                        $newPluginsConfig[] = [
                            'name' => $pluginName,
                            'config' => $pluginConfig,
                        ];
                    }
                    $place['pluginsConfig'] = $newPluginsConfig;
                }
            }
            $workflow['places'] = json_encode($workflow['places']);
            $this->addSql('UPDATE workflow SET places = :places WHERE id = :id', $workflow);
        }
    }
}
