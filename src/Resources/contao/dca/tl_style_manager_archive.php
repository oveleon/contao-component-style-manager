<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
*/

$GLOBALS['TL_DCA']['tl_style_manager_archive'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ctable'                      => array('tl_style_manager'),
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_style_manager_archive', 'checkPermission'),
            array('tl_style_manager_archive', 'checkIdentifier')
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
            'fields'                  => array('title'),
            'flag'                    => 1,
            'panelLayout'             => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields'                  => array('title'),
            'format'                  => '%s',
            'label_callback'          => array('tl_style_manager_archive', 'addIdentifier')
        ),
        'global_operations' => array
        (
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
            'edit' => array
            (
                'href'                => 'table=tl_style_manager',
                'icon'                => 'edit.svg'
            ),
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager_archive']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'header.svg',
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager_archive']['copy'],
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager_archive']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager_archive']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{title_legend},title,identifier;'
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
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager_archive']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'identifier' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager_archive']['identifier'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'rgxp'=>'variable', 'nospace'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        )
    )
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

use Oveleon\ContaoComponentStyleManager\StyleManagerArchiveModel;

class tl_style_manager_archive extends \Backend
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
     * Check permissions to edit table tl_style_manager_archive
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        return;
    }

    /**
     * Add identifier information
     *
     * @param array         $row
     * @param string        $label
     * @param DataContainer $dc
     * @param array         $args
     *
     * @return array
     */
    public function addIdentifier($row, $label, DataContainer $dc, $args)
    {
        if($row['identifier'])
        {
            $args[0] .= '<span style="color:#999;padding-left:3px">[' . $row['identifier'] . ']</span>';
        }

        return $args;
    }

    /**
     * Check identifier
     *
     * @param $dc
     */
    public function checkIdentifier($dc){
        $objArchive = StyleManagerArchiveModel::findById($dc->id);

        if($objArchive->identifier)
        {
            $GLOBALS['TL_DCA']['tl_style_manager_archive']['fields']['identifier']['eval']['mandatory'] = false;
            $GLOBALS['TL_DCA']['tl_style_manager_archive']['fields']['identifier']['eval']['disabled'] = true;
        }
    }
}
