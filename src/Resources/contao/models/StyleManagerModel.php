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
 * @property integer $tstamp
 * @property string  $title
 * @property string  $cssClasses
 * @property integer $extendPage
 * @property integer $extendArticle
 * @property integer $extendContentElement
 * @property integer $contentElements
 *
 * @method static StyleManagerModel|null findById($id, array $opt=array())
 * @method static StyleManagerModel|null findOneBy($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByCssClasses($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendPage($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendArticle($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByExtendContentElement($col, $val, $opt=array())
 * @method static StyleManagerModel|null findOneByContentElements($col, $val, $opt=array())
 *
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByCssClasses($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendPage($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendArticle($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByExtendContentElement($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findByContentElements($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerModel[]|StyleManagerModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 * @method static integer countByCssClasses($id, array $opt=array())
 * @method static integer countByExtendPage($id, array $opt=array())
 * @method static integer countByExtendArticle($id, array $opt=array())
 * @method static integer countByExtendContentElement($id, array $opt=array())
 * @method static integer countByContentElements($id, array $opt=array())
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
     * Find published news items by their parent ID
     *
     * @param array   $arrOptions An optional options array
     *
     * @return \Model\Collection|StyleManagerModel[]|StyleManagerModel|null A collection of models or null if there are no news
     */
    public static function findByTable($strTable, array $arrOptions=array())
    {
        $t = static::$strTable;

        switch ($strTable)
        {
            case 'tl_page':
                return static::findByExtendPage(1, $arrOptions);
            case 'tl_article':
                return static::findByExtendArticle(1, $arrOptions);
            case 'tl_content':
                return static::findByExtendContentElement(1, $arrOptions);
            default:
                return null;
        }
    }
}
