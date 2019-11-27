<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

// Back end modules
array_insert($GLOBALS['BE_MOD'], count($GLOBALS['BE_MOD']['design']), array
(
    'design' => array
    (
        'style_manager' => array
        (
            'tables'                => array('tl_style_manager')
        ),
        'style_manager_categories' => array
        (
            'tables'                => array('tl_style_manager_categories'),
            'hideInNavigation'      => true
        ),
    )
));

// Back end form fields
array_insert($GLOBALS['BE_FFL'], 1, array
(
    'stylemanager' => '\\Oveleon\\ContaoComponentStyleManager\\ComponentStyleSelect'
));

// Models
$GLOBALS['TL_MODELS']['tl_style_manager']            = '\\Oveleon\\ContaoComponentStyleManager\\StyleManagerModel';
$GLOBALS['TL_MODELS']['tl_style_manager_categories'] = '\\Oveleon\\ContaoComponentStyleManager\\StyleManagerCategoriesModel';

// Style sheet
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'bundles/contaocomponentstylemanager/stylemanager.css|static';
}
