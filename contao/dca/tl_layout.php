<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend fields
$GLOBALS['TL_DCA']['tl_layout']['fields']['styleManager'] = [
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => ['tl_class'=>'clr stylemanager'],
    'sql'                     => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_layout']['fields']['cssClass']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_layout']['fields']['cssClass']['eval']['alwaysSave'] = true;
