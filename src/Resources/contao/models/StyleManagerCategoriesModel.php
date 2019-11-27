<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

/**
 * Reads and writes fields from style manager categories
 *
 * @property integer $id
 * @property integer $tstamp
 * @property string  $title
 * @property string  $description
 *
 * @method static StyleManagerCategoriesModel|null findById($id, array $opt=array())
 * @method static StyleManagerCategoriesModel|null findOneBy($col, $val, $opt=array())
 * @method static StyleManagerCategoriesModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static StyleManagerCategoriesModel|null findOneByTitle($col, $val, $opt=array())
 *
 * @method static \Model\Collection|StyleManagerCategoriesModel[]|StyleManagerCategoriesModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerCategoriesModel[]|StyleManagerCategoriesModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerCategoriesModel[]|StyleManagerCategoriesModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerCategoriesModel[]|StyleManagerCategoriesModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

class StyleManagerCategoriesModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_style_manager_categories';
}
