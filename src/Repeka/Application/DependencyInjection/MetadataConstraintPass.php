<?php

namespace Repeka\Application\DependencyInjection;

use Repeka\Domain\Validation\MetadataConstraintProvider;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MetadataConstraintPass implements CompilerPassInterface {
    const TAG_NAME = 'repeka.metadata_constraint';

    public function process(ContainerBuilder $container) {
        if (!$container->has(MetadataConstraintProvider::class)) {
            return;
        }
        $providerDefinition = $container->findDefinition(MetadataConstraintProvider::class);
        $constraints = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($constraints) as $id) {
            $providerDefinition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
