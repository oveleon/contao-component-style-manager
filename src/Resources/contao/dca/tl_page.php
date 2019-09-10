<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend the regular palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('style_manager_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('styleManager'), 'style_manager_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('regular', 'tl_page');

// Extend fields
array_insert($GLOBALS['TL_DCA']['tl_page']['fields'], 0, array
(
    'styleManager' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['styleManager'],
        'exclude'                 => true,
        'inputType'               => 'stylemanager',
        'onload_callback'         => array
        (
            array('tl_style_manager_page', 'checkPermission')
        ),
        'save_callback'         => array
        (
            array('tl_style_manager_page', 'updateStyleManager')
        ),
        'eval'                    => array('tl_class'=>'clr stylemanager'),
        'sql'                     => "blob NULL"
    ),
    'styleManagerCompiled' => array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_page']['styleManagerCompiled'],
        'exclude'                 => true,
        'inputType'               => 'text',
        'sql'                     => "varchar(255) NOT NULL default ''"
    )
));

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */
class tl_style_manager_page extends \Backend
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
     * Check permissions to edit table tl_real_estate_group
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        return;
    }

    /**
     * Update StyleManager compiled-Field
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function updateStyleManager($varValue, DataContainer $dc)
    {
        $varValues = \StringUtil::deserialize($varValue, true);
        $varValues = array_filter($varValues);

        // Store the new classes
        $this->Database->prepare("UPDATE tl_page SET styleManagerCompiled=? WHERE id=?")
            ->execute(implode(' ', $varValues), $dc->id);

        return $varValue;
    }
}