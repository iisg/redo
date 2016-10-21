<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\UserInterface\DependencyInjection;

use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

abstract class BaseExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator($this->getConfigPath() . '/../Resources/config'));
        $loader->load('services.yml');
        $this->configureExtension($mergedConfig, $container);
    }

    private function getConfigPath() {
        $reflection = new ReflectionClass($this);
        return dirname($reflection->getFileName());
    }

    /**
     * @SuppressWarnings("unused")
     */
    protected function configureExtension(array $mergedConfig, ContainerBuilder $container) {
    }
}
