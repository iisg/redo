<?php
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel {
    public function registerBundles() {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new SimpleBus\SymfonyBridge\SimpleBusCommandBusBundle(),
            new SimpleBus\SymfonyBridge\SimpleBusEventBusBundle(),
            new Repeka\CoreModule\UserInterface\CoreModuleBundle(),
            new Repeka\FakeModule\UserInterface\FakeModuleBundle(),
            new \Repeka\WorkflowModule\Bundle\WorkflowModuleBundle()
        ];
        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
        }
        return $bundles;
    }

    public function getRootDir() {
        return __DIR__;
    }

    public function getCacheDir() {
        if (($env = getenv('REPEKA_CACHE_DIR')) !== false) {
            return $env . $this->getEnvironment();
        }
        return dirname(__DIR__) . '/var/cache/' . $this->getEnvironment();
    }

    public function getLogDir() {
        if (($env = getenv('REPEKA_LOG_DIR')) !== false) {
            return $env;
        }
        return dirname(__DIR__) . '/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
        if (file_exists($this->getRootDir() . '/config/config_local.yml')) {
            $loader->load($this->getRootDir() . '/config/config_local.yml');
        }
    }
}
