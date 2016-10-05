<?php
namespace Repeka\FakeModule\UserInterface\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(Extension::ALIAS);
        $rootNode->children()
            ->arrayNode('admin_emails_reply_to')
            ->info('List all emails to replay')
            ->prototype('scalar')->end()
            ->end()
            ->end();
        return $treeBuilder;
    }
}
