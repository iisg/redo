<?php
namespace Repeka\Application;

use Repeka\Application\DependencyInjection\ImportTransformsCompilerPass;
use Repeka\Application\DependencyInjection\MetadataConstraintCompilerPass;
use Repeka\Application\ParamConverter\MetadataValueProcessor\MetadataValueProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new MetadataConstraintCompilerPass());
        $container->addCompilerPass(new MetadataValueProcessorPass());
        $container->addCompilerPass(new ImportTransformsCompilerPass());
    }
}
