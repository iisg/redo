<?php
namespace Repeka\UserModule\Bundle\DependencyInjection;

use Repeka\CoreModule\Bundle\DependencyInjection\YmlExtensionConfigLoaderTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class UserModuleExtension extends Extension {
    use YmlExtensionConfigLoaderTrait;

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
    }
}
