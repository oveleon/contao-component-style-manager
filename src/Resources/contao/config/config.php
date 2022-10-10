<?php
use Contao\ArrayUtil;
use Oveleon\ContaoComponentStyleManager\StyleManager\Sync;
use Oveleon\ContaoComponentStyleManager\Widget\ComponentStyleSelect;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;

// Back end modules
ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], count($GLOBALS['BE_MOD']['design']), [
    'design' => [
        'style_manager' => [
            'tables'  => ['tl_style_manager_archive', 'tl_style_manager'],
            'export'  => [Sync::class, 'exportStyleManager'],
            'import'  => [Sync::class, 'importStyleManager']
        ]
    ]
]);

// Back end form fields
$GLOBALS['BE_FFL']['stylemanager'] = ComponentStyleSelect::class;

// Models
$GLOBALS['TL_MODELS']['tl_style_manager']         = StyleManagerModel::class;
$GLOBALS['TL_MODELS']['tl_style_manager_archive'] = StyleManagerArchiveModel::class;
