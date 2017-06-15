<?php
namespace Repeka\Application;

use Repeka\Application\DependencyInjection\MetadataConstraintPass;
use Repeka\Application\ParamConverter\MetadataValueProcessor\MetadataValueProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new MetadataConstraintPass());
        $container->addCompilerPass(new MetadataValueProcessorPass());
    }
}
