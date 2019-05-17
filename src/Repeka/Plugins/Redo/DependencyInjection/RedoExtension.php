<?php
namespace Repeka\Plugins\Redo\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class RedoExtension extends ConfigurableExtension {
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services-redo.yml');
        $container->setParameter('redo.pk_auth.enabled', $mergedConfig['pk_auth']['enabled']);
        $container->setParameter('redo.pk_auth.wsdl', $mergedConfig['pk_auth']['wsdl']);
        $container->setParameter('redo.pk_auth.options', $mergedConfig['pk_auth']['options']);
        $container->setParameter('redo.koha_url', $mergedConfig['koha_url']);
        $container->setParameter('redo.user_data_mapping', $mergedConfig['user_data_mapping']);
        $container->setParameter('redo.fts_config', $mergedConfig['fts']);
        $container->setParameter('redo.captcha_public_key', $mergedConfig['captcha']['public_key']);
        $container->setParameter('redo.captcha_private_key', $mergedConfig['captcha']['private_key']);
    }
}
