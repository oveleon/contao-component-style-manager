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
    )
));

// Back end form fields
array_insert($GLOBALS['BE_FFL'], 1, array
(
    'stylemanager' => '\\Oveleon\\ContaoComponentStyleManager\\ComponentStyleSelect'
));

// Models
$GLOBALS['TL_MODELS']['tl_style_manager'] = '\\Oveleon\\ContaoComponentStyleManager\\StyleManagerModel';

// Hooks
$GLOBALS['TL_HOOKS']['generatePage'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'generatePage');
$GLOBALS['TL_HOOKS']['getArticle'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'getArticle');
$GLOBALS['TL_HOOKS']['parseTemplate'][] = array('\\Oveleon\\ContaoComponentStyleManager\\StyleManager', 'parseTemplate');

// Style sheet
if (TL_MODE == 'BE')
{
    $GLOBALS['TL_CSS'][] = 'bundles/contaocomponentstylemanager/stylemanager.css|static';
}