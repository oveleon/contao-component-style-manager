<?php

namespace Oveleon\ContaoComponentStyleManager\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoComponentStyleManagerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('migrations.yaml');
        $loader->load('commands.yaml');
        $loader->load('services.yaml');

        $container->setParameter('contao_component_style_manager.use_bundle_config', $config['use_bundle_config']);
        $container->setParameter('contao_component_style_manager.strict', $config['strict']);
        $container->setParameter('contao_component_style_manager.invert_component_selection', $config['invert_component_selection']);
    }
}
