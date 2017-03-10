<?php
namespace Repeka\Application\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MetadataConstraintPass implements CompilerPassInterface {
    const PROVIDER_SERVICE = 'repeka.metadata_constraint_provider';
    const TAG_NAME = 'repeka.metadata_constraint';

    public function process(ContainerBuilder $container) {
        if (!$container->has(self::PROVIDER_SERVICE)) {
            return;
        }
        $providerDefinition = $container->findDefinition(self::PROVIDER_SERVICE);
        $constraints = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($constraints) as $id) {
            $providerDefinition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
