<?php
namespace Repeka\CoreModule\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class CoreModuleExtension extends Extension {
    use YmlExtensionConfigLoaderTrait;

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
    }
}
