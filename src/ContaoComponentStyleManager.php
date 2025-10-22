<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class ContaoComponentStyleManager extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
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
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        $builder->setParameter('contao_component_style_manager.use_bundle_config', $config['use_bundle_config']);
        $builder->setParameter('contao_component_style_manager.strict', $config['strict']);
        $builder->setParameter('contao_component_style_manager.show_group_title', $config['show_group_title']);
    }
}
