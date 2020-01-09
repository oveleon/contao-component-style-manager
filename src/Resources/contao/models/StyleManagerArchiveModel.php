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
 * @property string  $identifier
 * @property string  $groupAlias
 * @property string  $description
 *
 * @method static StyleManagerArchiveModel|null findById($id, array $opt=array())
 * @method static StyleManagerArchiveModel|null findOneBy($col, $val, $opt=array())
 * @method static StyleManagerArchiveModel|null findOneByTstamp($col, $val, $opt=array())
 * @method static StyleManagerArchiveModel|null findOneByTitle($col, $val, $opt=array())
 * @method static StyleManagerArchiveModel|null findOneByIdentifier($col, $val, $opt=array())
 *
 * @method static \Model\Collection|StyleManagerArchiveModel[]|StyleManagerArchiveModel|null findMultipleByIds($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerArchiveModel[]|StyleManagerArchiveModel|null findByTstamp($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerArchiveModel[]|StyleManagerArchiveModel|null findByTitle($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerArchiveModel[]|StyleManagerArchiveModel|null findByIdentifier($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerArchiveModel[]|StyleManagerArchiveModel|null findByGroupAlias($val, array $opt=array())
 * @method static \Model\Collection|StyleManagerArchiveModel[]|StyleManagerArchiveModel|null findAll(array $opt=array())
 *
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($id, array $opt=array())
 * @method static integer countByTitle($id, array $opt=array())
 * @method static integer countByIdentifier($id, array $opt=array())
 * @method static integer countByGroupAlias($id, array $opt=array())
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

class StyleManagerArchiveModel extends \Model
{

    /**
     * Table name
     * @var string
     */
    protected static $strTable = 'tl_style_manager_archive';
}
