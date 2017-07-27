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
        $container->setParameter('repeka.supported_controls', $mergedConfig['supported_controls']);
        $container->setParameter('repeka.max_nesting_depth', $mergedConfig['metadata_nesting_depth']);
        $container->setParameter('repeka.static_permissions', $mergedConfig['static_permissions']);
        $container->setParameter('elasticsearch.index_name', $mergedConfig['elasticsearch']['index_name']);
        $container->setParameter('elasticsearch.number_of_shards', $mergedConfig['elasticsearch']['number_of_shards']);
        $container->setParameter('elasticsearch.number_of_replicas', $mergedConfig['elasticsearch']['number_of_replicas']);
        $container->setParameter('elasticsearch.analyzers', $mergedConfig['elasticsearch']['analyzers']);
        $container->setParameter('elasticsearch.host', $mergedConfig['elasticsearch']['host']);
        $container->setParameter('elasticsearch.port', $mergedConfig['elasticsearch']['port']);
        $container->setParameter('elasticsearch.proxy', $mergedConfig['elasticsearch']['proxy']);
        $container->setParameter('repeka.upload.path', $mergedConfig['upload']['path']);
        $container->setParameter('repeka.upload.temp_folder', $mergedConfig['upload']['temp_folder']);
        $container->setParameter('pk_auth.wsdl', $mergedConfig['pk_auth']['wsdl']);
        $container->setParameter('pk_auth.options', $mergedConfig['pk_auth']['options']);
        $container->setParameter('pk_auth.local_accounts_enabled', $mergedConfig['pk_auth']['local_accounts_enabled']);
        $container->setParameter(
            'repeka.resource_classes',
            array_merge($mergedConfig['resource_classes'], SystemResourceClass::toArray())
        );
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
