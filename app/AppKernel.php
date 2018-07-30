<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
            new Repeka\Plugins\Ocr\RepekaOcrPluginBundle(),
            new Repeka\Plugins\MetadataValueSetter\RepekaMetadataValueSetterPluginBundle(),
        ];
        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Repeka\DeveloperBundle\DeveloperBundle();
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
        // optional webpack dev server: https://www.slideshare.net/nachomartin/webpacksf/60
        $loader->load(
            function ($container) {
                /** @var ContainerInterface $container */
                if ($this->getEnvironment() === 'dev' && $container->getParameter('use_webpack_dev_server')) {
                    $container->loadFromExtension(
                        'framework',
                        [
                            'assets' => [
                                'packages' => [
                                    'webpack' => [
                                        'base_urls' => ['http://localhost:7336'],
                                    ],
                                ],
                            ],
                        ]
                    );
                }
            }
        );
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
