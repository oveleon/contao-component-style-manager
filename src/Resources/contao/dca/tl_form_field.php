<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend fields
$GLOBALS['TL_DCA']['tl_form_field']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['sql'] = "text NOT NULL default ''";

$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'addPalette');
$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['child_record_callback'] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'listFormFields');
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
