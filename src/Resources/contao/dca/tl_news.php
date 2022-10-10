<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;

$bundles = Contao\System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoNewsBundle']))
{
    // Extend fields
    $GLOBALS['TL_DCA']['tl_news']['fields']['styleManager'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_news']['styleManager'],
        'exclude'                 => true,
        'inputType'               => 'stylemanager',
        'eval'                    => array('tl_class'=>'clr stylemanager'),
        'sql'                     => "blob NULL"
    );

    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['sql'] = "text NULL";
    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['eval']['alwaysSave'] = true;

    $GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = [StyleManager::class, 'addPalette'];
    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['load_callback'][] = [StyleManager::class, 'onLoad'];
    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['save_callback'][] = [StyleManager::class, 'onSave'];
}
