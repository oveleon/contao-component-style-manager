<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Model;

use Contao\Controller;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoComponentStyleManager\StyleManager\Config;
use Oveleon\ContaoComponentStyleManager\StyleManager\Sync;

/**
 * Reads and writes fields from style manager
 *
 * @property integer $id
 * @property integer $pid
 * @property integer $tstamp
 * @property string  $title
 * @property string  $alias
 * @property string  $category
 * @property string  $chosen
 * @property string  $blankOption
 * @property string  $passToTemplate
 * @property string  $cssClasses
 * @property string  $cssClass
 * @property integer $extendLayout
 * @property integer $extendPage
 * @property integer $extendModule
 * @property integer $extendArticle
 * @property integer $extendForm
 * @property integer $extendFormFields
 * @property integer $extendContentElement
 * @property integer $contentElements
 * @property integer $extendNews
 * @property integer $extendEvents
 *
 * @method static StyleManagerModel|null findById($id, array $opt=array())
 * @method static StyleManagerModel|null findByPid($id, array $opt=array())
 * @method static StyleManagerModel|null findOneBy($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByCssClasses($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByCategory($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendLayout($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendPage($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendModule($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendArticle($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendForm($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendFormFields($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendContentElement($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByContentElements($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendNews($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendEvents($col, $val, $opt=array())
 *
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByPids($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByTitle($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByCssClasses($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByCategory($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendLayout($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendPage($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendModule($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendArticle($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendForm($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendFormFields($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendContentElement($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByContentElements($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendNews($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendEvents($val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|StyleManagerModel[]|StyleManagerModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($id, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 * @method static integer countByCssClasses($id, array $opt=array())
 * @method static integer countByCategory($id, array $opt=array())
 * @method static integer countByExtendLayout($id, array $opt=array())
 * @method static integer countByExtendPage($id, array $opt=array())
 * @method static integer countByExtendModule($id, array $opt=array())
 * @method static integer countByExtendArticle($id, array $opt=array())
 * @method static integer countByExtendForm($id, array $opt=array())
 * @method static integer countByExtendFormFields($id, array $opt=array())
 * @method static integer countByExtendContentElement($id, array $opt=array())
 * @method static integer countByContentElements($id, array $opt=array())
 * @method static integer countByExtendNews($id, array $opt=array())
 * @method static integer countByExtendEvents($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class StyleManagerModel extends Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_style_manager';

    /**
     * Find published CSS groups using their table
     */
    public static function findByTable(string $strTable, array $arrOptions=array()): Collection|StyleManagerModel|array|null
    {
        switch ($strTable)
        {
            case 'tl_layout':
                return static::findByExtendLayout(1, $arrOptions);
            case 'tl_page':
                return static::findByExtendPage(1, $arrOptions);
            case 'tl_module':
                return static::findByExtendModule(1, $arrOptions);
            case 'tl_article':
                return static::findByExtendArticle(1, $arrOptions);
            case 'tl_form':
                return static::findByExtendForm(1, $arrOptions);
            case 'tl_form_field':
                return static::findByExtendFormFields(1, $arrOptions);
            case 'tl_content':
                return static::findByExtendContentElement(1, $arrOptions);
            case 'tl_news':
                return static::findByExtendNews(1, $arrOptions);
            case 'tl_calendar_events':
                return static::findByExtendEvents(1, $arrOptions);
            default:
                // ToDo: Maybe having an interface in the future would be better - The `styleManagerFindByTable` Hook has been removed

                return null;
        }
    }

    /**
     * Find configuration and published CSS groups using their table
     */
    public static function findByTableAndConfiguration(string $strTable, array $arrOptions=array()): Collection|StyleManagerModel|array|null
    {
        $objContainer = System::getContainer();
        $objGroups = static::findByTable($strTable, $arrOptions);

        // Load and merge bundle configurations
        if ($objContainer->getParameter('contao_component_style_manager.use_bundle_config'))
        {
            $arrObjStyleGroups = null;

            $bundleConfig = Config::getInstance();
            $arrGroups = $bundleConfig::getGroups($strTable);

            if (null !== $arrGroups)
            {
                $arrArchiveIdentifier = [];

                if (null !== ($objArchives = StyleManagerArchiveModel::findAll()))
                {
                    $arrArchiveIdentifier = array_combine(
                        $objArchives->fetchEach('id'),
                        $objArchives->fetchEach('identifier')
                    );
                }

                if (null !== $objGroups)
                {
                    foreach ($objGroups as $objGroup)
                    {
                        $alias = $arrArchiveIdentifier[$objGroup->pid] . '_' . $objGroup->alias;

                        $arrObjStyleGroups[ $alias ] = $objGroup->current();
                    }
                }

                // Append bundle config groups
                foreach ($arrGroups as $combinedAlias => $objGroup)
                {
                    $blnStrict = $objContainer->getParameter('contao_component_style_manager.strict');

                    // Skip if the alias already exists in the backend configuration
                    if ($blnStrict && $arrObjStyleGroups && !\array_key_exists($combinedAlias, $arrObjStyleGroups))
                    {
                        $arrObjStyleGroups[ $combinedAlias ] = $objGroup;
                    }
                    elseif (!$blnStrict)
                    {
                        // Merge if the alias already exists in the backend configuration
                        if ($arrObjStyleGroups && \array_key_exists($combinedAlias, $arrObjStyleGroups))
                        {
                            // Overwrite with a merged object
                            $arrObjStyleGroups[ $combinedAlias ] = self::mergeGroupObjects($objGroup, $arrObjStyleGroups[ $combinedAlias ], ['id', 'pid', 'alias']);
                        }
                        else
                        {
                            $arrObjStyleGroups[ $combinedAlias ] = $objGroup;
                        }
                    }
                }
            }

            if ($arrObjStyleGroups)
            {
                // Sort by sorting
                usort($arrObjStyleGroups, function($a, $b) {
                    return ($a->sorting <=> $b->sorting);
                });

                return $arrObjStyleGroups;
            }
        }

        return $objGroups;
    }

    /**
     * Find one item by alias by their parent ID
     */
    public static function findByAliasAndPid(string $alias, int|string $pid, array $arrOptions=array()): StyleManagerModel|null
    {
        $t = static::$strTable;

        $arrColumns = array(
            "$t.alias=?",
            "$t.pid=?"
        );

        $arrValues = array(
            $alias,
            $pid
        );

        return static::findOneBy($arrColumns, $arrValues, $arrOptions);
    }

    private static function mergeGroupObjects(StyleManagerModel|null $objOriginal = null, StyleManagerModel|null $objMerge = null, array|null $skipFields = null, bool $skipEmpty = true, $forceOverwrite = true): StyleManagerModel|null
    {
        if (null === $objOriginal || null === $objMerge)
        {
            return $objOriginal;
        }

        Controller::loadDataContainer('tl_style_manager');

        foreach ($objMerge->row() as $field => $value)
        {
            if (
                ($skipEmpty && (!$value || strtolower((string) $value) === 'null'))
                || (null !== $skipFields && in_array($field, $skipFields))
            ) {
                continue;
            }

            switch ($field)
            {
                // Merge and manipulation of existing classes
                case 'cssClasses':
                    if ($objOriginal->{$field})
                    {
                        /** @var array $arrClasses */
                        $arrClasses = StringUtil::deserialize($objOriginal->{$field}, true);
                        $arrExists = self::flattenKeyValueArray($arrClasses);

                        /** @var array $arrValues */
                        $arrValues = StringUtil::deserialize($value, true);

                        foreach ($arrValues as $cssClass)
                        {
                            if (array_key_exists($cssClass['key'], $arrExists))
                            {
                                if (!$forceOverwrite)
                                {
                                    continue;
                                }

                                // Overwrite existing value
                                if (false !== ($key = array_search($cssClass['key'], array_column($arrClasses, 'key'))))
                                {
                                    $arrClasses[ $key ] = [
                                        'key' => $cssClass['key'],
                                        'value' => $cssClass['value']
                                    ];
                                }

                                continue;
                            }

                            $arrClasses[] = [
                                'key' => $cssClass['key'],
                                'value' => $cssClass['value']
                            ];
                        }

                        $value  = serialize($arrClasses);
                    }

                    break;
                // Check for multiple fields like contentElement
                default:
                    $fieldOptions = $GLOBALS['TL_DCA']['tl_style_manager']['fields'][$field];

                    if (isset($fieldOptions['eval']['multiple']) && !!$fieldOptions['eval']['multiple'] && $fieldOptions['inputType'] === 'checkbox')
                    {
                        /** @var array $arrElements */
                        $arrElements = StringUtil::deserialize($objOriginal->{$field}, true);

                        /** @var array $arrValues */
                        $arrValues = StringUtil::deserialize($value, true);

                        foreach ($arrValues as $element)
                        {
                            if (in_array($element, $arrElements))
                            {
                                if (!$forceOverwrite)
                                {
                                    continue;
                                }

                                $key = array_search($element, $arrElements);
                                $arrElements[ $key ] = $element;

                                continue;
                            }

                            $arrElements[] = $element;
                        }

                        $value  = serialize($arrElements);
                    }
            }

            // Overwrite field values
            $objOriginal->{$field} = $value;
        }

        return $objOriginal;
    }
}
