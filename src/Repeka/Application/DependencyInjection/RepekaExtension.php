<?php
namespace Repeka\Application\DependencyInjection;

use ReflectionClass;
use Repeka\Domain\Constants\SystemResourceClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class RepekaExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
        $container->setParameter('repeka.default_ui_language', $mergedConfig['default_ui_language']);
        $container->setParameter('repeka.fallback_ui_languages', $mergedConfig['fallback_ui_languages']);
        $container->setParameter('repeka.max_nesting_depth', $mergedConfig['metadata_nesting_depth']);
        $container->setParameter('repeka.static_permissions', $mergedConfig['static_permissions']);
        $container->setParameter('elasticsearch.index_name', $mergedConfig['elasticsearch']['index_name']);
        $container->setParameter('elasticsearch.number_of_shards', $mergedConfig['elasticsearch']['number_of_shards']);
        $container->setParameter('elasticsearch.number_of_replicas', $mergedConfig['elasticsearch']['number_of_replicas']);
        $container->setParameter('elasticsearch.analyzers', $mergedConfig['elasticsearch']['analyzers']);
        $container->setParameter('elasticsearch.stop_words', $mergedConfig['elasticsearch']['stop_words']);
        $container->setParameter('elasticsearch.host', $mergedConfig['elasticsearch']['host']);
        $container->setParameter('elasticsearch.port', $mergedConfig['elasticsearch']['port']);
        $container->setParameter('elasticsearch.proxy', $mergedConfig['elasticsearch']['proxy']);
        $container->setParameter('repeka.metadata_groups', $mergedConfig['metadata_groups']);
        $container->setParameter('repeka.upload_dirs', $mergedConfig['upload_dirs']);
        $container->setParameter('repeka.version', $mergedConfig['version']);
        $container->setParameter('repeka.audit', $mergedConfig['audit']);
        $container->setParameter('repeka.exposed_endpoints', $mergedConfig['expose_endpoints']);
        $container->setParameter('repeka.templates_resource_class', $mergedConfig['templating']['templates_resource_class']);
        $container->setParameter('repeka.theme', $mergedConfig['templating']['theme']);
        $this->retrieveResourceClassesParameters($mergedConfig, $container);
    }

    private function loadYmlConfigFile(string $name, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator($this->getConfigPath() . '/../Resources/config'));
        $loader->load($name . '.yml');
    }

    private function getConfigPath() {
        $reflection = new ReflectionClass($this);
        return dirname($reflection->getFileName());
    }

    private function retrieveResourceClassesParameters(array $mergedConfig, ContainerBuilder $container): void {
        $resourceClasses = $mergedConfig['resource_classes'];
        $resourceClassesNames = array_column($mergedConfig['resource_classes'], 'name');
        foreach (SystemResourceClass::toArray() as $systemResourceClassName) {
            $systemResourceClassConfig = SystemResourceClass::toSystemResourceClassConfig($systemResourceClassName);
            if (!in_array($systemResourceClassConfig['name'], $resourceClassesNames)) {
                $resourceClasses[] = [
                    'name' => $systemResourceClassConfig['name'],
                    'icon' => $systemResourceClassConfig['icon'],
                    'admins' => [],
                    'operators' => [],
                ];
            }
        }
        $resourceClassesConfig = [];
        $resourceClassesIcons = [];
        foreach ($resourceClasses as $resourceClassConfig) {
            $resourceClassesConfig[$resourceClassConfig['name']] = array_merge(['admins' => [], 'operators' => []], $resourceClassConfig);
            $resourceClassesIcons = array_merge($resourceClassesIcons, [$resourceClassConfig['name'] => $resourceClassConfig['icon']]);
        }
        $resourceClassesNames = array_column($resourceClasses, 'name');
        $container->setParameter('repeka.resource_classes', $resourceClassesNames);
        $container->setParameter('repeka.resource_classes_icons', $resourceClassesIcons);
        $container->setParameter('repeka.resource_classes_config', $resourceClassesConfig);
    }
}
