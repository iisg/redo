<?php
namespace Repeka\Plugins\EmailSender\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class RepekaEmailSenderPluginExtension extends ConfigurableExtension {

    /**
     * @inheritdoc
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services-email-sender.yml');
        $container->setParameter(
            'repeka_plugin_email_sender.smtp_address',
            $mergedConfig['smtp_address']
        );
        $container->setParameter(
            'repeka_plugin_email_sender.smtp_port',
            $mergedConfig['smtp_port']
        );
        $container->setParameter(
            'repeka_plugin_email_sender.smtp_username',
            $mergedConfig['smtp_username']
        );
        $container->setParameter(
            'repeka_plugin_email_sender.smtp_password',
            $mergedConfig['smtp_password']
        );
        $container->setParameter(
            'repeka_plugin_email_sender.smtp_encryption',
            $mergedConfig['smtp_encryption']
        );
        $container->setParameter(
            'repeka_plugin_email_sender.from_email',
            $mergedConfig['from_email']
        );
        $container->setParameter(
            'repeka_plugin_email_sender.from_name',
            $mergedConfig['from_name']
        );
    }
}
