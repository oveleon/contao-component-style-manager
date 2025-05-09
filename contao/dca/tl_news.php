<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Contao\System;

$bundles = System::getContainer()->getParameter('kernel.bundles');

if (isset($bundles['ContaoNewsBundle']))
{
    // Extend fields
    $GLOBALS['TL_DCA']['tl_news']['fields']['styleManager'] = [
        'exclude'                 => true,
        'inputType'               => 'stylemanager',
        'eval'                    => ['tl_class'=>'clr stylemanager'],
        'sql'                     => "blob NULL"
    ];

    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['sql'] = "text NULL";
    $GLOBALS['TL_DCA']['tl_news']['fields']['cssClass']['eval']['alwaysSave'] = true;
}
