<?php
namespace Repeka\FakeModule\UserInterface\DependencyInjection;

use Repeka\CoreModule\UserInterface\DependencyInjection\ServicesLoaderExtension;
use Repeka\CoreModule\UserInterface\DependencyInjection\YmlExtensionConfigLoaderTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class Extension extends ConfigurableExtension {
    use YmlExtensionConfigLoaderTrait;

    const ALIAS = 'fake_module';

    public function getAlias() {
        return self::ALIAS;
    }

    /**
     * @inheritdoc
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services', $container);
        $this->processAdminEmails($mergedConfig['admin_emails_reply_to'], $container);
    }

    private function processAdminEmails(array $adminEmails, ContainerBuilder $container) {
        $container->setParameter('repeka.fake_module.admin_email_list', $adminEmails);
    }
}
