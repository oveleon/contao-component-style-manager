<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

// Extend fields
$GLOBALS['TL_DCA']['tl_form']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_form']['fields']['attributes']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_form']['fields']['attributes']['eval']['alwaysSave'] = true;

$GLOBALS['TL_DCA']['tl_form']['config']['onload_callback'][] = [StyleManager::class, 'addPalette'];
$GLOBALS['TL_DCA']['tl_form']['fields']['attributes']['load_callback'][] = [StyleManager::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_form']['fields']['attributes']['save_callback'][] = [StyleManager::class, 'onSave'];
