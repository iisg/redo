<?php
namespace Repeka\FakeModule\UserInterface;

use Repeka\FakeModule\UserInterface\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class FakeModuleBundle extends Bundle {
    public function getContainerExtension() {
        if (null === $this->extension) {
            $this->extension = new Extension();
        }
        return $this->extension;
    }
}
