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

foreach ($GLOBALS['TL_DCA']['tl_form_field']['palettes'] as $key=>$value){
    if($key === '__selector__')
    {
        continue;
    }

    $palette->applyToPalette($key, 'tl_form_field');
}

// Extend fields
$GLOBALS['TL_DCA']['tl_form_field']['fields']['styleManager'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => array('tl_class'=>'clr stylemanager'),
    'sql'                     => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
