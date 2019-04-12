<?php
namespace Repeka\Application\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /** @inheritdoc */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('repeka');
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        $rootNode
            ->children()
                ->scalarNode('default_ui_language')->defaultValue('en')->end()
                ->arrayNode('fallback_ui_languages')->scalarPrototype()->end()->end()
                ->scalarNode('version')->defaultValue('X.X.X')->end()
                ->scalarNode('version_full')->defaultValue('X')->end()
                ->arrayNode('static_permissions')->prototype('scalar')->end()->end()
                ->integerNode('metadata_nesting_depth')->min(1)->end()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->scalarNode('index_name')->end()
                        ->integerNode('number_of_shards')->min(1)->defaultValue(1)->end()
                        ->integerNode('number_of_replicas')->min(0)->defaultValue(0)->end()
                        ->arrayNode('analyzers')->useAttributeAsKey('name')->prototype('scalar')->end()->end()
                        ->arrayNode('stop_words')->useAttributeAsKey('name')->prototype('scalar')->end()->end()
                        ->scalarNode('host')->defaultValue('elasticsearch')->cannotBeEmpty()->end()
                        ->integerNode('port')->defaultValue(9200)->end()
                        ->scalarNode('proxy')->defaultValue(null)->end()
                    ->end()
                ->end()
                ->arrayNode('resource_classes')->requiresAtLeastOneElement()->arrayPrototype()->children()
                    ->scalarNode('name')->isRequired()->end()
                    ->variableNode('icon')->defaultValue('book')->end()
                    ->variableNode('admins')->validate()->castToArray()->end()->end()
                    ->variableNode('operators')->validate()->castToArray()->end()->end()
                ->end()->end()->end()
                ->arrayNode('metadata_groups')->arrayPrototype()->children()
                    ->scalarNode('id')->isRequired()->end()
                    ->arrayNode('label')->useAttributeAsKey('anything-required-by-symfony')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()->end()->end()
                ->arrayNode('upload_dirs')->arrayPrototype()->children()
                    ->scalarNode('id')->isRequired()->end()
                    ->scalarNode('label')->isRequired()->end()
                    ->scalarNode('path')->isRequired()->end()
                    ->scalarNode('condition')->defaultValue(null)->end()
                    ->scalarNode('canBeUsedInResources')->defaultValue(true)->end()
                ->end()->end()->end()
                ->arrayNode('templating')->addDefaultsIfNotSet()->children()
                    ->scalarNode('templates_resource_class')->defaultValue(null)->end()
                    ->scalarNode('theme')->defaultValue('')->end()
                ->end()->end()
                ->arrayNode('audit')->defaultValue([])->arrayPrototype()->children()
                    ->scalarNode('id')->isRequired()->end()
                    ->scalarNode('url')->defaultValue('')->end()
                    ->arrayNode('label')->useAttributeAsKey('anything-required-by-symfony')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()->end()->end()
                ->arrayNode('expose_endpoints')->normalizeKeys(false)->defaultValue([])->arrayPrototype()->children()
                    ->scalarNode('metadata')->defaultValue(null)->end()
                    ->scalarNode('template')->defaultValue(null)->end()
                    ->scalarNode('endpointUsageTrackingKey')->defaultValue(null)->end()
                    ->integerNode('resourceId')->defaultValue(null)->end()
                    ->arrayNode('headers')->normalizeKeys(false)->defaultValue([])->prototype('scalar')->end()->end()
                ->end()->end()->end()
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
