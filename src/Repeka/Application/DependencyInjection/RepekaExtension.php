<?php
namespace Repeka\Application\DependencyInjection;

use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class RepekaExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
        $container->setParameter('data_module.supported_controls', $mergedConfig['supported_controls']);
        $container->setParameter('data_module.max_nesting_depth', $mergedConfig['metadata_nesting_depth']);
        $container->setParameter('elasticsearch.index_name', $mergedConfig['elasticsearch']['index_name']);
        $container->setParameter('elasticsearch.number_of_shards', $mergedConfig['elasticsearch']['number_of_shards']);
        $container->setParameter('elasticsearch.number_of_replicas', $mergedConfig['elasticsearch']['number_of_replicas']);
        $container->setParameter('elasticsearch.analyzers', $mergedConfig['elasticsearch']['analyzers']);
        $container->setParameter('elasticsearch.host', $mergedConfig['elasticsearch']['host']);
        $container->setParameter('elasticsearch.port', $mergedConfig['elasticsearch']['port']);
        $container->setParameter('elasticsearch.proxy', $mergedConfig['elasticsearch']['proxy']);
    }

    private function loadYmlConfigFile(string $name, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator($this->getConfigPath() . '/../Resources/config'));
        $loader->load($name . '.yml');
    }

    private function getConfigPath() {
        $reflection = new ReflectionClass($this);
        return dirname($reflection->getFileName());
    }
}
