<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

use Contao\Backend;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;

class StyleManager
{
    public const VARS_KEY = '__vars__';

    /**
     * Valid CSS-Class fields [field => size]
     */
    public static array $validCssClassFields = [
        'cssID'      => 2,
        'cssClass'   => 1,
        'class'      => 1,
        'attributes' => 2
    ];

    /**
     * Load callback for the CSS-Classes DCA-Field
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function onLoad($varValue, $dc)
    {
        return self::clearClasses($varValue, $dc);
    }

    /**
     * Save callback for the CSS-Classes DCA-Field
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function onSave($varValue, $dc)
    {
        return self::updateClasses($varValue, $dc);
    }

    /**
     * Adding the StyleManager fields and palette to a dca
     *
     * @param $dc
     */
    public function addPalette($dc): void
    {
        $palette = PaletteManipulator::create()
            ->addLegend('style_manager_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE)
            ->addField(['styleManager'], 'style_manager_legend', PaletteManipulator::POSITION_APPEND);

        foreach ($GLOBALS['TL_DCA'][ $dc->table ]['palettes'] as $key=>$value){
            if($key === '__selector__')
            {
                continue;
            }

            $palette->applyToPalette($key, $dc->table);
        }
    }

    /**
     * Clear StyleManager classes from css class field
     *
     * @param mixed $varValue
     * @param $dc
     *
     * @return mixed
     */
    public static function clearClasses(mixed $varValue, $dc)
    {
        if(self::isMultipleField($dc->field))
        {
            $cssID = StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1] ?? '';
        }

        $arrValues = StringUtil::deserialize($dc->activeRecord->styleManager, true);
        $arrValues = self::deserializeValues($arrValues);

        // remove non-exiting values
        self::cleanupClasses($arrValues, $dc->table);

        if(count($arrValues))
        {
            foreach ($arrValues as $k => $v)
            {
                $arrValues[ $k ] = ' ' . $v . ' ';
            }

            $varValue = ' ' . $varValue . ' ';
            $varValue = str_replace($arrValues, '  ', $varValue);
            $varValue = trim(preg_replace('#\s+#', ' ', $varValue));
        }

        if(self::isMultipleField($dc->field))
        {
            $varValue = serialize(array(($cssID[0] ?? ''), $varValue));
        }

        return $varValue;
    }

    /**
     * Update StyleManager classes
     *
     * @param mixed $varValue
     * @param $dc
     *
     * @return mixed
     */
    public static function updateClasses(mixed $varValue, $dc)
    {
        if(self::isMultipleField($dc->field))
        {
            $cssID = StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1] ?? '';
        }

        $varValues = StringUtil::deserialize($dc->activeRecord->styleManager, true);

        // remove vars node
        if(isset($varValues[StyleManager::VARS_KEY]))
        {
            unset($varValues[StyleManager::VARS_KEY]);
        }

        // append classes
        $varValue .= ($varValue ? ' ' : '') . (count($varValues) ? implode(' ', $varValues) : '');
        $varValue  = trim($varValue);

        if(self::isMultipleField($dc->field))
        {
            $varValue = serialize(array(($cssID[0] ?? ''), $varValue));
        }

        return $varValue;
    }

    /**
     * Reset all StyleManager classes from css class field
     *
     * @param mixed $varValue
     * @param $dc
     * @param $strTable
     *
     * @return mixed
     */
    public static function resetClasses(mixed $varValue, $dc, $strTable)
    {
        if(self::isMultipleField($dc->field))
        {
            $cssID = StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1] ?? '';
        }

        $objStyles = StyleManagerModel::findByTableAndConfiguration($strTable);
        $arrStyles = array();

        if($objStyles !== null)
        {
            foreach($objStyles as $objStyle)
            {
                $arrGroup = StringUtil::deserialize($objStyle->cssClasses, true);

                foreach ($arrGroup as $opts)
                {
                    $arrStyles[] = ' ' . $opts['key'] . ' ';
                }
            }

            $arrStyles = array_filter($arrStyles);
        }

        if(count($arrStyles))
        {
            $varValue  = ' ' . $varValue . ' ';

            $varValue = str_replace($arrStyles, ' ', $varValue);
            $varValue = trim((string) preg_replace('#\s+#', ' ', $varValue));
        }

        if(self::isMultipleField($dc->field))
        {
            $varValue = serialize(array(($cssID[0] ?? ''), $varValue));
        }

        return $varValue;
    }

    /**
     * Checks the passed array and removes non-existent values
     *
     * @param $arrValues
     * @param $strTable
     */
    public static function cleanupClasses(&$arrValues, $strTable): void
    {
        if(is_array($arrValues))
        {
            $objStyles = StyleManagerModel::findByTableAndConfiguration($strTable);

            if($objStyles !== null)
            {
                $arrExistingKeys = array();
                $arrExistingValues = array();
                $arrArchives = array();

                $objStyleArchives = StyleManagerArchiveModel::findAllWithConfiguration();

                // Prepare archives identifier
                foreach($objStyleArchives as $objStyleArchive)
                {
                    $arrArchives[ $objStyleArchive->id ] =  $objStyleArchive->identifier;
                }

                foreach($objStyles as $objStyle)
                {
                    $arrExistingKeys[] = self::generateAlias($arrArchives[ $objStyle->pid ], $objStyle->alias);

                    $arrGroup = StringUtil::deserialize($objStyle->cssClasses, true);

                    foreach ($arrGroup as $opts)
                    {
                        $arrExistingValues[] = $opts['key'];
                    }
                }

                foreach ($arrValues as $key => $value)
                {
                    if(!in_array($key, $arrExistingKeys))
                    {
                        unset($arrValues[$key]);
                        continue;
                    }

                    if(!in_array($value, $arrExistingValues))
                    {
                        unset($arrValues[$key]);
                    }
                }
            }
            else
            {
                $arrValues = array();
            }
        }
    }

    /**
     * Return the field name of css classes by table
     *
     * @param $strTable
     *
     * @return mixed
     */
    public static function getClassFieldNameByTable($strTable)
    {
        Backend::loadDataContainer($strTable);

        foreach (self::$validCssClassFields as $field => $size)
        {
            if(isset($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $field ]))
            {
                return $field;
            }
        }

        return false;
    }

    /**
     * Checks whether a field is multiple
     *
     * @param $strField
     *
     * @return bool
     */
    public static function isMultipleField($strField)
    {
        return self::$validCssClassFields[ $strField ] > 1;
    }

    /**
     * Moves classes which should be passed to the template to the "vars" node
     *
     * @param $varValue
     * @param $strTable
     *
     * @return array|bool
     */
    public static function serializeValues($varValue, $strTable)
    {
        $objStyleGroups = StyleManagerModel::findByTableAndConfiguration($strTable);

        if($objStyleGroups === null)
        {
            return false;
        }

        $arrArchives = array();
        $objStyleArchives = StyleManagerArchiveModel::findAllWithConfiguration();

        // Prepare archives identifier
        foreach($objStyleArchives as $objStyleArchive)
        {
            $arrArchives[ $objStyleArchive->id ] =  $objStyleArchive->identifier;
        }

        // Remove unused classes
        $arrValue = array_filter($varValue, function($v){
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });

        // Rebuild array for template variables
        foreach($objStyleGroups as $objStyleGroup)
        {
            $strId = self::generateAlias($arrArchives[ $objStyleGroup->pid ], $objStyleGroup->alias);

            if(array_key_exists($strId, $arrValue))
            {
                if(!!$objStyleGroup->passToTemplate)
                {
                    $identifier = $arrArchives[ $objStyleGroup->pid ];

                    $arrValue[ StyleManager::VARS_KEY ][ $identifier ][ $objStyleGroup->alias ] = array(
                        'id'    => $objStyleGroup->id,
                        'value' => $arrValue[ $strId ]
                    );

                    unset($arrValue[ $strId ]);
                }
            }
        }

        return $arrValue;
    }

    /**
     * Restore the default value of the StyleManager Widget (without vars node)
     *
     * @param $arrValue
     *
     * @return mixed
     */
    public static function deserializeValues($arrValue)
    {
        if(isset($arrValue[ StyleManager::VARS_KEY ]))
        {
            foreach ($arrValue[ StyleManager::VARS_KEY ] as $archiveAlias => $values)
            {
                foreach ($values as $alias => $arrItem)
                {
                    $strId = self::generateAlias($archiveAlias, $alias);
                    $arrValue[$strId] = html_entity_decode((string) $arrItem['value']);
                }
            }

            unset($arrValue[ StyleManager::VARS_KEY ]);
        }

        return $arrValue;
    }

    /**
     * Generate a unique alias based on the archive identifier and the group alias
     */
    public static function generateAlias($identifier, $alias): string
    {
        return $identifier . '_' . $alias;
    }

    /**
     * Check whether an element is visible in style manager widget
     */
    public static function isVisibleGroup(StyleManagerModel $objGroup, string $strTable): bool
    {
        if(
            'tl_layout' === $strTable && !!$objGroup->extendLayout ||
            'tl_page' === $strTable && !!$objGroup->extendPage ||
            'tl_module' === $strTable && !!$objGroup->extendModule ||
            'tl_article' === $strTable && !!$objGroup->extendArticle ||
            'tl_form' === $strTable && !!$objGroup->extendForm ||
            'tl_form_field' === $strTable && !!$objGroup->extendFormFields ||
            'tl_content' === $strTable && !!$objGroup->extendContentElement ||
            'tl_news' === $strTable && !!$objGroup->extendNews ||
            'tl_calendar_events' === $strTable && !!$objGroup->extendEvents
        ){ return true; }

        // Check is visible group for custom configurations
        if (isset($GLOBALS['TL_HOOKS']['styleManagerIsVisibleGroup']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerIsVisibleGroup']))
        {
            foreach ($GLOBALS['TL_HOOKS']['styleManagerIsVisibleGroup'] as $callback)
            {
                return System::importStatic($callback[0])->{$callback[1]}($objGroup, $strTable);
            }
        }

        return false;
    }

    /**
     * Add the type of input field
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function listFormFields($arrRow)
    {
        $arrStyles = StringUtil::deserialize($arrRow['styleManager']);
        $arrRow['styleManager'] = new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);

        $formField = new \tl_form_field();
        return $formField->listFormFields($arrRow);
    }
}
