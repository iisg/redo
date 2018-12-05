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
        $container->setParameter('repeka.application_url', $mergedConfig['application_url']);
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
        $container->setParameter('pk_auth.wsdl', $mergedConfig['pk_auth']['wsdl']);
        $container->setParameter('pk_auth.options', $mergedConfig['pk_auth']['options']);
        $container->setParameter('pk_auth.local_accounts_enabled', $mergedConfig['pk_auth']['local_accounts_enabled']);
        $container->setParameter('xml_import.koha', $mergedConfig['xml_import']['koha']);
        $container->setParameter('user_data_mapping', $mergedConfig['user_data_mapping']);
        $container->setParameter('repeka.metadata_groups', $mergedConfig['metadata_groups']);
        $container->setParameter('repeka.upload_dirs', $mergedConfig['upload_dirs']);
        $container->setParameter('repeka.version', $mergedConfig['version']);
        $container->setParameter('repeka.exposed_endpoints', $mergedConfig['expose_endpoints']);
        $this->retrieveResourceClassesParameters($mergedConfig, $container);
        $this->retrieveTemplatingParameters($mergedConfig, $container);
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

    private function retrieveTemplatingParameters(array $mergedConfig, ContainerBuilder $container) {
        $templating = $mergedConfig['templating'] ?? [];
        $container->setParameter('repeka.templates_resource_class', $templating['templates_resource_class'] ?? null);
        $templates = ['login_form' => 'login-form.twig', 'homepage' => 'home.twig', 'error_page' => 'error-page.twig'];
        foreach ($templates as $templateName => $defaultTemplateView) {
            $container->setParameter(
                'repeka.templates.' . $templateName,
                ($templating['templates'] ?? [])[$templateName] ?? $defaultTemplateView
            );
        }
    }
}
