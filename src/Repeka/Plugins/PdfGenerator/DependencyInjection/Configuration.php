<?php
namespace Repeka\Plugins\PdfGenerator\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('repeka_pdf_generator_plugin');
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        $rootNode
            ->children()
                ->scalarNode('targetResourceDirectoryId')->defaultValue('resourceFiles')->end()
                ->scalarNode('wkHtmlToPdfPath')->defaultValue('/wkhtmltox/bin/wkhtmltopdf')->end()
          ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
