<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

$bundles = Contao\System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoCalendarBundle']))
{
    // Extend fields
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['styleManager'] = array
    (
        'label'                   => &$GLOBALS['TL_LANG']['tl_calendar_events']['styleManager'],
        'exclude'                 => true,
        'inputType'               => 'stylemanager',
        'eval'                    => array('tl_class'=>'clr stylemanager'),
        'sql'                     => "blob NULL"
    );

    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['cssClass']['sql'] = "text NULL";
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['cssClass']['eval']['alwaysSave'] = true;

    $GLOBALS['TL_DCA']['tl_calendar_events']['config']['onload_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'addPalette');
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['cssClass']['load_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onLoad');
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['cssClass']['save_callback'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'onSave');
}
