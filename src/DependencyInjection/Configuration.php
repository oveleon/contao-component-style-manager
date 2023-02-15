<?php

namespace Oveleon\ContaoComponentStyleManager\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('contao_component_style_manager');

        if (\method_exists($treeBuilder, 'getRootNode')) {
            $rootNode = $treeBuilder->getRootNode();
        } else {
            // Backwards compatibility
            $rootNode = $treeBuilder->root('contao_component_style_manager');
        }

        $rootNode
            ->children()
                ->booleanNode('use_bundle_config')
                    ->defaultTrue()
                ->end()
                ->booleanNode('strict')
                    ->defaultFalse()
                ->end()
                ->booleanNode('show_group_title')
                    ->defaultTrue()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
