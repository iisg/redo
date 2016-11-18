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
        $container->setParameter('data_module.supported_languages', $mergedConfig['supported_languages']);
        $container->setParameter('data_module.main_language', $mergedConfig['supported_languages'][0]);
        $container->setParameter('data_module.supported_controls', $mergedConfig['supported_controls']);
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
