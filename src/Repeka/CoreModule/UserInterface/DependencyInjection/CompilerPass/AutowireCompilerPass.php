<?php
declare (strict_types = 1);
namespace Repeka\CoreModule\UserInterface\DependencyInjection\CompilerPass;

use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AutowireCompilerPass implements CompilerPassInterface {
    public function process(ContainerBuilder $container) {
        if (!$container->hasDefinition('simple_bus.command_bus')) {
            return;
        }
        $definition = $container->findDefinition('simple_bus.command_bus');
        $definition->addAutowiringType(MessageBus::class);
    }
}
