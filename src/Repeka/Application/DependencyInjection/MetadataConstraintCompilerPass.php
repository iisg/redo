<?php
namespace Repeka\Application\DependencyInjection;

use Repeka\Domain\Validation\MetadataConstraintManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MetadataConstraintCompilerPass implements CompilerPassInterface {
    const TAG_NAME = 'repeka.metadata_constraint';

    public function process(ContainerBuilder $container) {
        if (!$container->has(MetadataConstraintManager::class)) {
            return;
        }
        $providerDefinition = $container->findDefinition(MetadataConstraintManager::class);
        $constraints = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($constraints) as $id) {
            $providerDefinition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
