<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

// Extend fields
$GLOBALS['TL_DCA']['tl_form_field']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['eval']['alwaysSave'] = true;

$GLOBALS['TL_DCA']['tl_form_field']['config']['onload_callback'][] = [StyleManager::class, 'addPalette'];
$GLOBALS['TL_DCA']['tl_form_field']['list']['sorting']['child_record_callback'] = [StyleManager::class, 'listFormFields'];
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['load_callback'][] = [StyleManager::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['save_callback'][] = [StyleManager::class, 'onSave'];
