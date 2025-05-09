<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Extend fields
$GLOBALS['TL_DCA']['tl_form_field']['fields']['styleManager'] = [
    'label'                   => &$GLOBALS['TL_LANG']['tl_form_field']['styleManager'],
    'exclude'                 => true,
    'inputType'               => 'stylemanager',
    'eval'                    => ['tl_class'=>'clr stylemanager'],
    'sql'                     => "blob NULL"
];

$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['sql'] = "text NULL";
$GLOBALS['TL_DCA']['tl_form_field']['fields']['class']['eval']['alwaysSave'] = true;
