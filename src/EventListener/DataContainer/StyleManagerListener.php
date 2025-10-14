<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener\DataContainer;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;

/**
 * @internal
 */
class StyleManagerListener
{
    #[AsCallback(table: 'tl_style_manager', target: 'config.onload')]
    public function checkRegisteredBundles(): void
    {
        $bundles = System::getContainer()->getParameter('kernel.bundles');

        if (!isset($bundles['ContaoCalendarBundle']))
        {
            unset($GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendEvents']);
        }

        if (!isset($bundles['ContaoNewsBundle']))
        {
            unset($GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendNews']);
        }
    }

    #[AsCallback(table: 'tl_style_manager', target: 'list.sorting.child_record')]
    public function listGroupRecords(array $row): string
    {
        $arrExtends = null;
        $label = $row['title'];

        if ($row['passToTemplate'])
        {
            $parent = StyleManagerArchiveModel::findById($row['pid']);

            $label = vsprintf('<span class="sm_list_token var" title="$this->styleManager->get(\'%s\', [\'%s\'])">$</span> %s', [
                $parent->identifier,
                $row['alias'],
                $label
            ]);
        }
        else
        {
            $label = sprintf('<span class="sm_list_token">C</span> %s', $label);
        }

        foreach ($row as $field => $value)
        {
            if (str_starts_with($field, 'extend') && !!$value)
            {
                $arrExtends[] = &$GLOBALS['TL_LANG']['tl_style_manager'][ $field ][0];
            }
        }

        if ($arrExtends !== null)
        {
            $label .= sprintf('<span style="color:#999;padding-left:3px">[%s]</span>', implode(", ", $arrExtends));
        }

        return $label;
    }

    #[AsCallback(table: 'tl_style_manager', target: 'fields.alias.save')]
    public function generateAlias($varValue, DataContainer $dc): string
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            $objDatabase = Database::getInstance();

            return $objDatabase->prepare("SELECT id FROM tl_style_manager WHERE alias=? AND id!=? AND pid=?")->execute($alias, $dc->id, $dc->activeRecord->pid)->numRows > 0;
        };

        // Generate an alias if there is none
        if ($varValue == '')
        {
            $varValue = System::getContainer()->get('contao.slug')->generate($dc->activeRecord->title, [], $aliasExists);
        }
        elseif ($aliasExists($varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['groupAliasExists'], $varValue));
        }

        return $varValue;
    }

    #[AsCallback(table: 'tl_style_manager', target: 'fields.cssClasses.load')]
    public function translateKeyValue($varValue)
    {
        Controller::loadLanguageFile('tl_style_manager');

        $GLOBALS['TL_LANG']['MSC']['ow_key'] = $GLOBALS['TL_LANG']['tl_style_manager']['ow_key'];
        $GLOBALS['TL_LANG']['MSC']['ow_value'] = $GLOBALS['TL_LANG']['tl_style_manager']['ow_value'];

        return $varValue;
    }

    #[AsCallback(table: 'tl_style_manager', target: 'fields.formFields.options')]
    public function getFormFields(): array
    {
        Controller::loadLanguageFile('tl_form_field');

        $arrFields = $GLOBALS['TL_FFL'];

        // Add the translation
        foreach (array_keys($arrFields) as $key)
        {
            $arrFields[$key] = $GLOBALS['TL_LANG']['FFL'][$key][0];
        }

        return $arrFields;
    }

    #[AsCallback(table: 'tl_style_manager', target: 'fields.contentElements.options')]
    public function getContentElements(): array
    {
        $groups = [];

        foreach ($GLOBALS['TL_CTE'] as $k => $v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }

    #[AsCallback(table: 'tl_style_manager', target: 'fields.modules.options')]
    public function getModules(): array
    {
        $groups = [];

        foreach ($GLOBALS['FE_MOD'] as $k => $v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }
}
