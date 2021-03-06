<?php

use Repeka\Application\DependencyInjection\WithBundleDependencies;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel {
    const APP_PATH = __DIR__;
    const VAR_PATH = __DIR__ . '/../var';

    public function registerBundles() {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new M6Web\Bundle\StatsdBundle\M6WebStatsdBundle(),
            new Repeka\Application\RepekaBundle(),
        ];
        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Repeka\DeveloperBundle\DeveloperBundle();
        }
        $bundles = array_merge($bundles, $this->getPluginsBundles());
        return $bundles;
    }

    private function getPluginsBundles(): array {
        $finder = new Finder();
        $finder->files()
            ->in(self::APP_PATH . '/../src/Repeka/Plugins/*/')
            ->name('*Bundle.php')
            ->depth('==0');
        $pluginBundles = [];
        foreach ($finder as $pluginBundle) {
            /** @var \SplFileInfo $pluginBundle */
            $path = $pluginBundle->getRealPath();
            $pluginClassName = substr(basename($path), 0, -4); // cut .php
            $namespace = basename(dirname($path));
            $fullClassName = 'Repeka\\Plugins\\' . $namespace . '\\' . $pluginClassName;
            $bundle = new $fullClassName();
            $pluginBundles[] = $bundle;
            if ($bundle instanceof WithBundleDependencies) {
                $pluginBundles = array_merge($pluginBundles, $bundle->getDependentBundles());
            }
        }
        return $pluginBundles;
    }

    public function getRootDir() {
        return __DIR__;
    }

    public function getCacheDir() {
        if (($env = getenv('REPEKA_CACHE_DIR')) !== false) {
            return $env . $this->getEnvironment();
        }
        return self::VAR_PATH . '/cache/' . $this->getEnvironment();
    }

    public function getLogDir() {
        if (($env = getenv('REPEKA_LOG_DIR')) !== false) {
            return $env;
        }
        return self::VAR_PATH . '/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader) {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');
        $localConfigPath = self::VAR_PATH . '/config/config_local.yml';
        if (file_exists($localConfigPath)) {
            $loader->load($localConfigPath);
        }
    }

    protected function build(\Symfony\Component\DependencyInjection\ContainerBuilder $container) {
        parent::build($container);
        if ($this->getEnvironment() === 'test') {
            $container->addCompilerPass(
                new Repeka\Tests\TestContainerPass(),
                \Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_OPTIMIZE
            );
        }
    }
}
