<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend the regular palette
$palette = Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('style_manager_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('styleManager'), 'style_manager_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_article');

// Extend fields
$GLOBALS['TL_DCA']['tl_article']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL",
    'save_callback'           => array(
        array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'updateOnMultiEdit')
    )
);

$GLOBALS['TL_DCA']['tl_article']['fields']['cssID']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'clearStyleManager');
$GLOBALS['TL_DCA']['tl_article']['fields']['cssID']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'updateStyleManager');
