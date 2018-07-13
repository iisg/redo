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
        $container->setParameter('xml_import.koha', $mergedConfig['xml_import']['koha']);
        $container->setParameter('user_data_mapping', $mergedConfig['user_data_mapping']);
        $container->setParameter('repeka.version', $mergedConfig['version']);
        $container->setParameter('repeka.webpack_hashes', $mergedConfig['webpack_hashes']);
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
            if (!in_array($systemResourceClassName, $resourceClassesNames)) {
                $resourceClasses[] = [
                    'name' => $systemResourceClassName,
                    'admins' => [],
                    'operators' => [],
                ];
            }
        }
        $resourceClassesConfig = [];
        foreach ($resourceClasses as $resourceClassConfig) {
            $resourceClassesConfig[$resourceClassConfig['name']] = array_merge(['admins' => [], 'operators' => []], $resourceClassConfig);
        }
        $resourceClassesNames = array_column($resourceClasses, 'name');
        $container->setParameter('repeka.resource_classes', $resourceClassesNames);
        $container->setParameter('repeka.resource_classes_config', $resourceClassesConfig);
    }
}
