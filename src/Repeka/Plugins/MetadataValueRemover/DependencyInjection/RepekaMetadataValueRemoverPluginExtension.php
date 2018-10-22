<?php
namespace Repeka\Plugins\MetadataValueRemover\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class RepekaMetadataValueRemoverPluginExtension extends Extension {
    /** @inheritdoc */
    public function load(array $configs, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services-metadata-value-remover.yml');
    }
}
