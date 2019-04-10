<?php
namespace Repeka\Plugins\EmailSender\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('repeka_email_sender_plugin');
        // @codingStandardsIgnoreStart
        // @formatter:off because indentation makes config structure way clearer
        $rootNode
            ->children()
            ->scalarNode('smtp_address')->defaultValue('localhost')->end()
            ->integerNode('smtp_port')->defaultValue(25)->end()
            ->scalarNode('smtp_username')->defaultValue('')->end()
            ->scalarNode('smtp_password')->defaultValue('')->end()
            ->scalarNode('smtp_encryption')->defaultValue(null)->end()
            ->scalarNode('from_email')->defaultValue('mailer@repeka.local')->end()
            ->scalarNode('from_name')->defaultValue('')->end()
            ->end();
        // @formatter:on
        // @codingStandardsIgnoreEnd
        return $treeBuilder;
    }
}
