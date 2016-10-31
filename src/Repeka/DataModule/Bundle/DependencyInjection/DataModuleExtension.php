<?php
namespace Repeka\DataModule\Bundle\DependencyInjection;

use Repeka\CoreModule\Bundle\DependencyInjection\YmlExtensionConfigLoaderTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DataModuleExtension extends ConfigurableExtension {
    use YmlExtensionConfigLoaderTrait;

    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
        $container->setParameter('data_module.supported_languages', $mergedConfig['supported_languages']);
        $container->setParameter('data_module.supported_controls', $mergedConfig['supported_controls']);
    }
}
