<?php
namespace Repeka\Plugins\Redo\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /** @inheritdoc */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('redo');
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        $rootNode
            ->canBeDisabled()
            ->children()
                ->arrayNode('pk_auth')->canBeEnabled()
                    ->children()
                        ->scalarNode('wsdl')->defaultNull()->end()
                        ->variableNode('options')->defaultValue([])->end()
                    ->end()
                ->end()
                ->scalarNode('koha_url')
                  ->defaultValue('http://koha.biblos.pk.edu.pl/cgi-bin/koha/opac-export-simple.pl?op=export&format=marcxml&skip_entity_encoding=1&barcode=')
                ->end()
                ->scalarNode('user_data_mapping')->defaultValue(\AppKernel::APP_PATH . '/../var/config/user_data_mapping.json')->end()
                ->arrayNode('fts')->addDefaultsIfNotSet()->children()
                    ->arrayNode('searchable_metadata_ids')->prototype('scalar')->defaultValue([])->end()->end()
                    ->arrayNode('filterable_metadata_ids')->prototype('scalar')->defaultValue([])->end()->end()
                    ->arrayNode('searchable_resource_classes')->prototype('scalar')->defaultValue([])->end()->end()
                    ->arrayNode('facets')->prototype('scalar')->defaultValue([])->end()->end()
                ->end()->end()
                ->arrayNode('deposit')->addDefaultsIfNotSet()->children()
                    ->arrayNode('relationship_resource_kind_ids')->prototype('scalar')->defaultValue([])->end()->end()
                ->end()->end()
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
