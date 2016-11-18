<?php
namespace Repeka\Application\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('data_module');
        $rootNode
            ->children()
            ->arrayNode('supported_languages')->prototype('scalar')->end()->end()
            ->arrayNode('supported_controls')->prototype('scalar')->end()->end()
            ->end();
        return $treeBuilder;
    }
}
