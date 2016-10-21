<?php
namespace Repeka\WorkflowModule\Bundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class WorkflowModuleExtension extends Extension implements PrependExtensionInterface {
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container) {
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function prepend(ContainerBuilder $container) {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../Resources/config/workflow.yml'));
        $container->prependExtensionConfig('framework', $config['framework']);
    }
}
