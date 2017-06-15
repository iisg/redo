<?php
namespace Repeka\Application\ParamConverter\MetadataValueProcessor;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class MetadataValueProcessorPass implements CompilerPassInterface {
    const PROCESSOR_SERVICE = 'repeka.metadata_value_processor';
    const TAG_NAME = 'repeka.metadata_value_processor';

    public function process(ContainerBuilder $container) {
        if (!$container->has(self::PROCESSOR_SERVICE)) {
            return;
        }
        $serviceDefinition = $container->findDefinition(self::PROCESSOR_SERVICE);
        $processors = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($processors) as $id) {
            $serviceDefinition->addMethodCall('registerStrategy', [new Reference($id)]);
        }
    }
}
