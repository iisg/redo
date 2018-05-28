<?php
namespace Repeka\Plugins\MetadataValueSetter\DependencyInjection;

use ReflectionClass;
use Repeka\Application\Service\DisplayStrategies\TwigResourceDisplayStrategyEvaluator;
use Repeka\Domain\Entity\ResourceEntity;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class RepekaMetadataValueSetterPluginExtension extends Extension {
    /** @inheritdoc */
    public function load(array $configs, ContainerBuilder $container) {
        $this->loadYmlConfigFile('services-metadata-value-setter', $container);
    }

    private function loadYmlConfigFile(string $name, ContainerBuilder $container) {
        $loader = new Loader\YamlFileLoader($container, new FileLocator($this->getConfigPath() . '/../Resources/config'));
        $loader->load($name . '.yml');
    }

    private function getConfigPath() {
        $reflection = new ReflectionClass($this);
        return dirname($reflection->getFileName());
    }

    public function getMetadataInputValue(ResourceEntity $resourceEntity, String $template): String {
        $evaluator = new TwigResourceDisplayStrategyEvaluator();
        return $evaluator->render($resourceEntity, $template);
    }
}
