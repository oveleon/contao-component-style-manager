<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

// Extend fields
$GLOBALS['TL_DCA']['tl_layout']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_layout']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_layout']['fields']['cssClass']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_layout']['fields']['cssClass']['eval']['alwaysSave'] = true;

$GLOBALS['TL_DCA']['tl_layout']['config']['onload_callback'][] = [StyleManager::class, 'addPalette'];
$GLOBALS['TL_DCA']['tl_layout']['fields']['cssClass']['load_callback'][] = [StyleManager::class, 'onLoad'];
$GLOBALS['TL_DCA']['tl_layout']['fields']['cssClass']['save_callback'][] = [StyleManager::class, 'onSave'];
