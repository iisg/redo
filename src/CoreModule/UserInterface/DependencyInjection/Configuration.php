<?php
namespace Repeka\CoreModule\UserInterface\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface {
    public function getConfigTreeBuilder() {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root(Extension::ALIAS);
        return $treeBuilder;
    }
}
