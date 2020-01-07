<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

$bundles = Contao\System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoNewsBundle']))
{
    // Extend the regular palette
    $palette = Contao\CoreBundle\DataContainer\PaletteManipulator::create()
        ->addLegend('style_manager_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
        ->addField(array('styleManager'), 'style_manager_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
        ->applyToPalette('default', 'tl_news');

    // Extend fields
    $GLOBALS['TL_DCA']['tl_news']['fields']['styleManager'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_news']['styleManager'],
        'exclude'                 => true,
        'inputType'               => 'stylemanager',
        'eval'                    => array('tl_class'=>'clr stylemanager'),
        'sql'                     => "blob NULL"
    );

    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
}
