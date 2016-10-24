<?php
namespace Repeka\CoreModule\UserInterface\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class Extension extends ConfigurableExtension {
    use YmlExtensionConfigLoaderTrait;

    const ALIAS = 'core_module';

    public function getAlias() {
        return self::ALIAS;
    }

    /**
     * @inheritdoc
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
    }
}
