<?php

declare(strict_types=1);

namespace Oveleon\ContaoComponentStyleManager\DependencyInjection\StyleManager;

use Composer\InstalledVersions;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StyleManagerContaoCallbackPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $class = StyleManager::class;

        if (!$container->hasDefinition($class)) {
            return;
        }

        $styleManager = $container->getDefinition($class);

        $styleManager->addTag('contao.callback', [
            'table'  => 'tl_form_field',
            'method' => 'listFormFields',
            'target' => $this->isLegacyContao()
                ? 'list.sorting.child_record'
                : 'list.label.label'
            ,
        ]);
    }

    private function isLegacyContao(): bool
    {
        $version = InstalledVersions::getVersion('contao/core-bundle');
        return version_compare($version ?? '0.0.0', '5.7.0', '<');
    }
}
