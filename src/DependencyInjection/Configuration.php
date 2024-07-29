<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('contao_component_style_manager');
        $treeBuilder
            ->getRootNode()
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
