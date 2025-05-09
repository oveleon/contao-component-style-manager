<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Contao\System;

$bundles = System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoCalendarBundle']))
{
    // Extend fields
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['styleManager'] = [
        'exclude'                 => true,
        'inputType'               => 'stylemanager',
        'eval'                    => ['tl_class'=>'clr stylemanager'],
        'sql'                     => "blob NULL"
    ];

    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['cssClass']['sql'] = "text NULL";
    $GLOBALS['TL_DCA']['tl_calendar_events']['fields']['cssClass']['eval']['alwaysSave'] = true;
}
