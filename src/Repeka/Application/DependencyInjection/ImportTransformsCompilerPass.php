<?php
namespace Repeka\Application\DependencyInjection;

use Repeka\Domain\MetadataImport\Transform\ImportTransformComposite;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ImportTransformsCompilerPass implements CompilerPassInterface {
    const TAG_NAME = 'repeka.import_transform';

    public function process(ContainerBuilder $container) {
        if (!$container->has(ImportTransformComposite::class)) {
            return;
        }
        $providerDefinition = $container->findDefinition(ImportTransformComposite::class);
        $constraints = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach (array_keys($constraints) as $id) {
            $providerDefinition->addMethodCall('register', [new Reference($id)]);
        }
    }
}
