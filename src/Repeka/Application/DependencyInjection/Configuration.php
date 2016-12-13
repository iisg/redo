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
        // @formatter:off because indentation makes config structure way clearer
        $rootNode
            ->children()
                ->arrayNode('supported_controls')->prototype('scalar')->end()->end()
                ->integerNode('metadata_nesting_depth')->min(1)->end()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->scalarNode('index_name')->end()
                        ->integerNode('number_of_shards')->min(1)->defaultValue(1)->end()
                        ->integerNode('number_of_replicas')->min(0)->defaultValue(0)->end()
                        ->arrayNode('analyzers')->useAttributeAsKey('name')->prototype('scalar')->end()->end()
                        ->scalarNode('host')->defaultValue('localhost')->cannotBeEmpty()->end()
                        ->integerNode('port')->defaultValue(9200)->end()
                        ->scalarNode('proxy')->defaultValue('')->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on
        return $treeBuilder;
    }
}
