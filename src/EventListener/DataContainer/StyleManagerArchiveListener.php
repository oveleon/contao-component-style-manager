<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\StringUtil;
use Oveleon\ContaoComponentStyleManager\Controller\BackendModule\ImportController;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\StyleManager\Config;
use Oveleon\ContaoComponentStyleManager\StyleManager\ConfigurationFileType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 */
readonly class StyleManagerArchiveListener
{
    public function __construct(
        private RouterInterface $router,

        #[Autowire(param: 'contao_component_style_manager.use_bundle_config')]
        private string $bundleConfig,
    ) {
    }

    #[AsCallback(table: 'tl_style_manager_archive', target: 'config.onload')]
    public function checkIdentifier($dc): void
    {
        $objArchive = StyleManagerArchiveModel::findById($dc->id);

        if (null !== $objArchive && $objArchive->identifier)
        {
            $GLOBALS['TL_DCA']['tl_style_manager_archive']['fields']['identifier']['eval']['mandatory'] = false;
            $GLOBALS['TL_DCA']['tl_style_manager_archive']['fields']['identifier']['eval']['disabled'] = true;
        }
    }

    #[AsCallback(table: 'tl_style_manager_archive', target: 'list.label.label')]
    public function addIdentifierInfo($row, $label)
    {
        if ($row['identifier'])
        {
            $label .= sprintf('<span style="color:#999;padding-left:3px">[%s]</span>', $row['identifier']);
        }

        return $label;
    }

    #[AsCallback(table: 'tl_style_manager_archive', target: 'list.global_operations.import.button')]
    public function importConfigButton(string|null $href, string $label, string $title, string $class, string $attributes): string
    {
        if ($this->bundleConfig && ($arrFiles = Config::getBundleConfigurationFiles(ConfigurationFileType::XML)))
        {
            $label .= sprintf(' <sup><small>(%s)</small></sup>', count($arrFiles));
        }

        return vsprintf('<a href="%s" class="%s" title="%s" %s>%s</a> ', [
            $this->router->generate(ImportController::class),
            $class,
            StringUtil::specialchars($title),
            $attributes,
            $label
        ]);
    }

    #[AsCallback(table: 'tl_style_manager_archive', target: 'list.global_operations.config.button')]
    public function bundleConfigButton(string|null $href, string $label, string $title, string $class, string $attributes): string
    {
        if (!$this->bundleConfig)
        {
            return '';
        }

        $count = 0;

        if ($arrFiles = Config::getBundleConfigurationFiles(ConfigurationFileType::XML))
        {
            $count = count($arrFiles);
        }

        return vsprintf('<a href="%s" class="%s" %s>%s: %s</a>', [
            $this->router->generate(ImportController::class),
            $class,
            $attributes,
            $label,
            $count
        ]);
    }

    #[AsCallback(table: 'tl_style_manager_archive', target: 'fields.identifier.save')]
    public function generateIdentifier($varValue, DataContainer $dc): string
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            $objDatabase = Database::getInstance();

            return $objDatabase->prepare("SELECT id FROM tl_style_manager_archive WHERE identifier=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        if ($aliasExists($varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['identifierExists'], $varValue));
        }

        return $varValue;
    }
}
