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
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
