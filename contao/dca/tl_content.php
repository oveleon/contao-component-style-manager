<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend fields
$GLOBALS['TL_DCA']['tl_content']['fields']['styleManager'] = [
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => ['tl_class'=>'clr stylemanager'],
    'sql'                     => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['cssID']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_content']['fields']['cssID']['eval']['alwaysSave'] = true;
