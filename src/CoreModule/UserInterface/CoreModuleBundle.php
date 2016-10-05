<?php
namespace Repeka\CoreModule\UserInterface;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Repeka\CoreModule\UserInterface\DependencyInjection\Extension;
use Repeka\CoreModule\UserInterface\DependencyInjection\CompilerPass\AutowireCompilerPass;

class CoreModuleBundle extends Bundle {
    public function build(ContainerBuilder $container) {
        parent::build($container);
        $container->addCompilerPass(new AutowireCompilerPass());
    }

    public function getContainerExtension() {
        if (null === $this->extension) {
            $this->extension = new Extension();
        }
        return $this->extension;
    }
}