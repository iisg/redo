<?php
namespace Repeka\FakeModule\UserInterface\DependencyInjection;

use Repeka\CoreModule\UserInterface\DependencyInjection\BaseExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Extension extends BaseExtension {
    const ALIAS = 'fake_module';

    public function getAlias() {
        return self::ALIAS;
    }

    protected function configureExtension(array $mergedConfig, ContainerBuilder $container) {
        $this->processAdminEmails($mergedConfig['admin_emails_reply_to'], $container);
    }

    private function processAdminEmails(array $adminEmails, ContainerBuilder $container) {
        $container->setParameter('repeka.fake_module.admin_email_list', $adminEmails);
    }
}
