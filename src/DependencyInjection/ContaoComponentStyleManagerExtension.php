<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoComponentStyleManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );

        $loader->load('migrations.yaml');
        $loader->load('commands.yaml');
        $loader->load('services.yaml');

        $container->setParameter('contao_component_style_manager.use_bundle_config', $config['use_bundle_config']);
        $container->setParameter('contao_component_style_manager.strict', $config['strict']);
        $container->setParameter('contao_component_style_manager.show_group_title', $config['show_group_title']);
    }
}
