<?php
namespace Repeka\UserModule\Bundle\DependencyInjection;

use Repeka\CoreModule\Bundle\DependencyInjection\YmlExtensionConfigLoaderTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UserModuleExtension extends Extension {
    use YmlExtensionConfigLoaderTrait;

    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
    }
}
