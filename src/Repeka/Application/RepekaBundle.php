<?php
namespace Repeka\Application;

use Repeka\Application\DependencyInjection\MetadataConstraintPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class RepekaBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        $container->addCompilerPass(new MetadataConstraintPass());
    }
}
