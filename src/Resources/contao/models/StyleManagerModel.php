<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

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
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByPids($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByCssClasses($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByCategory($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendLayout($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendPage($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendModule($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendArticle($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendForm($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendFormFields($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendContentElement($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByContentElements($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendNews($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendEvents($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findAll(array $opt=array())
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

class StyleManagerModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_style_manager';

    /**
     * Find published css groups using their table
     *
     * @param $strTable
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|StyleManagerModel[]|StyleManagerModel|null A collection of models or null if there are no css groups
     */
    public static function findByTable($strTable, array $arrOptions=array())
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
                // HOOK: add support for third-party tables
                if (isset($GLOBALS['TL_HOOKS']['styleManagerFindByTable']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerFindByTable']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['styleManagerFindByTable'] as $callback)
                    {
                        if (null !== ($result = \Contao\System::importStatic($callback[0])->{$callback[1]}($strTable, $arrOptions)))
                        {
                            return $result;
                        }
                    }
                }

                return null;
        }
    }

    /**
     * Find one item by alias by their parent ID
     *
     * @param $alias
     * @param $pid
     * @param array $arrOptions
     *
     * @return StyleManagerModel|null
     */
    public static function findByAliasAndPid($alias, $pid, $arrOptions=array())
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
}
