<?php
namespace Repeka\FakeModule\UserInterface;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Repeka\FakeModule\UserInterface\DependencyInjection\Extension;

class FakeModuleBundle extends Bundle {
    public function getContainerExtension() {
        if (null === $this->extension) {
            $this->extension = new Extension();
        }
        return $this->extension;
    }
}