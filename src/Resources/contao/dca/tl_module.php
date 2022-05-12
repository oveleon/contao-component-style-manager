<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend fields
$GLOBALS['TL_DCA']['tl_module']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cssID']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_module']['fields']['cssID']['eval']['alwaysSave'] = true;

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'addPalette');
$GLOBALS['TL_DCA']['tl_module']['fields']['cssID']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
$GLOBALS['TL_DCA']['tl_module']['fields']['cssID']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
