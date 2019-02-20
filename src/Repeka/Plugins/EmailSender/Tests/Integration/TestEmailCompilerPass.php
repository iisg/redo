<?php
namespace Repeka\Plugins\EmailSender\Tests\Integration;

use Repeka\Plugins\EmailSender\Model\EmailSender;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TestEmailCompilerPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        $container->register(TestEmailSender::class, TestEmailSender::class);
        $container->setAlias(EmailSender::class, TestEmailSender::class);
    }
}
