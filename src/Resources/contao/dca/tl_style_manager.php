<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
*/

$GLOBALS['TL_DCA']['tl_style_manager'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_style_manager', 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 1,
            'fields'                  => array('category'),
            'flag'                    => 11,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'format'                  => '%s'
        ),
        'global_operations' => array
        (
            'categories' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['categories'],
                'href'                => 'do=style_manager_categories',
                'icon'                => 'rows.svg',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('extendContentElement'),
        'default'                     => '{title_legend},title,description,category;{config_legend},cssClasses;{publish_legend},extendPage,extendArticle,extendContentElement;{expert_legend:hide},alias,chosen;'
    ),

    // Sub-Palettes
    'subpalettes' => array
    (
        'extendContentElement'        => 'contentElements'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['alias'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('rgxp'=>'folderalias', 'doNotCopy'=>true, 'maxlength'=>128, 'tl_class'=>'w50'),
            'save_callback' => array
            (
                array('tl_style_manager', 'generateAlias')
            ),
            'sql'                     => "varchar(255) COLLATE utf8_bin NOT NULL default ''"
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['description'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'category' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['category'],
            'inputType'               => 'select',
            'options_callback'        => array('tl_style_manager', 'getAllCategories'),
            'eval'                    => array('chosen'=>true, 'tl_class'=>'w50', 'includeBlankOption'=>true),
            'sql'                     => "varchar(64) NOT NULL default ''"
        ),
        'cssClasses' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['cssClasses'],
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('allowHtml'=>true, 'tl_class'=>'clr long'),
            'load_callback'           => array(
                array('tl_style_manager', 'prepareData'),
                array('tl_style_manager', 'translateKeyValue')
            ),
            'sql'                     => "blob NULL"
        ),
        'chosen' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['chosen'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default '1'"
        ),
        'extendPage' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendPage'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendArticle' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendArticle'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendContentElement' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendContentElement'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr', 'submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'contentElements' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['contentElements'],
            'inputType'               => 'checkbox',
            'options_callback'        => array('tl_style_manager', 'getContentElements'),
            'reference'               => &$GLOBALS['TL_LANG']['CTE'],
            'eval'                    => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50'),
            'sql'                     => "blob NULL"
        ),
    )
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

use Oveleon\ContaoComponentStyleManager\StyleManagerCategoriesModel;

class tl_style_manager extends \Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_style_manager
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        return;
    }

    /**
     * Returns all allowed page types as array
     *
     * @param DataContainer $dc
     *
     * @return array
     */
    public function getAllCategories(DataContainer $dc)
    {
        $arrCategories = StyleManagerCategoriesModel::findAll();
        $arrResult = array();

        if($arrCategories !== null)
        {
            while($arrCategories->next())
            {
                $arrResult[] = $arrCategories->title;
            }
        }

        return $arrResult;
    }

    /**
     * Auto-generate the news alias if it has not been set yet
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '')
        {
            $autoAlias = true;
            $varValue = \StringUtil::generateAlias($dc->activeRecord->title);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_style_manager WHERE alias=? AND id!=?")
            ->execute($varValue, $dc->id);

        // Check whether the styles alias exists
        if ($objAlias->numRows)
        {
            if (!$autoAlias)
            {
                throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
            }

            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * Prepare data from older versions
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function translateKeyValue($varValue, \DataContainer $dc)
    {
        $GLOBALS['TL_LANG']['MSC']['ow_key'] = $GLOBALS['TL_LANG']['tl_style_manager']['ow_key'];
        $GLOBALS['TL_LANG']['MSC']['ow_value'] = $GLOBALS['TL_LANG']['tl_style_manager']['ow_value'];

        return $varValue;
    }

    /**
     * Prepare data from older versions
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function prepareData($varValue, \DataContainer $dc)
    {
        $arrValue = \StringUtil::deserialize($varValue);

        if($arrValue !== null)
        {
            if(!isset($arrValue[0]['key']))
            {
                foreach ($arrValue as &$item) {
                    $item = array(
                        'key' => $item,
                        'value' => ''
                    );
                }
            }
        }

        return serialize($arrValue);
    }

    /**
     * Return all content elements as array
     *
     * @return array
     */
    public function getContentElements()
    {
        $groups = array();

        foreach ($GLOBALS['TL_CTE'] as $k=>$v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }
}
