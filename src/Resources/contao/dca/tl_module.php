<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend the regular palette
$palette = Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('style_manager_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('styleManager'), 'style_manager_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND);

foreach ($GLOBALS['TL_DCA']['tl_module']['palettes'] as $key=>$value){
    if($key === '__selector__')
    {
        continue;
    }

    $palette->applyToPalette($key, 'tl_module');
}

// Extend fields
$GLOBALS['TL_DCA']['tl_module']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL",
    'save_callback'           => array(
        array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'updateOnMultiEdit')
    )
);

$GLOBALS['TL_DCA']['tl_module']['fields']['cssID']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'clearStyleManager');
$GLOBALS['TL_DCA']['tl_module']['fields']['cssID']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'updateStyleManager');
