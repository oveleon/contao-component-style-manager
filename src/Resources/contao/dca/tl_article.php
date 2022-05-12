<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend fields
$GLOBALS['TL_DCA']['tl_article']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_article']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_article']['fields']['cssID']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_article']['fields']['cssID']['eval']['alwaysSave'] = true;

$GLOBALS['TL_DCA']['tl_article']['config']['onload_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'addPalette');
$GLOBALS['TL_DCA']['tl_article']['fields']['cssID']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
$GLOBALS['TL_DCA']['tl_article']['fields']['cssID']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
