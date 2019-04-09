<?php
namespace Repeka\Plugins\EmailSender\Tests\Integration;

use Repeka\Domain\Service\ResourceDisplayStrategyEvaluator;
use Repeka\Plugins\EmailSender\Model\EmailSender;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class TestEmailCompilerPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        $def = $container->register(TestEmailSender::class, TestEmailSender::class);
        $def->addArgument(new Reference(ResourceDisplayStrategyEvaluator::class));
        $container->setAlias(EmailSender::class, TestEmailSender::class);
    }
}
