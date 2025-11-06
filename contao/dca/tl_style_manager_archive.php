<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_style_manager_archive'] = [
    // Config
    'config' => [
        'dataContainer'               => DC_Table::class,
        'ctable'                      => ['tl_style_manager'],
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'sql' => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'                    => DataContainer::MODE_SORTED,
            'fields'                  => ['groupAlias', 'sorting'],
            'panelLayout'             => 'filter;search,limit'
        ],
        'label' => [
            'fields'                  => ['title'],
            'format'                  => '%s'
        ],
        'global_operations' => [
            'config' => [
                'class'               => 'header_style_manager_config',
                'primary'             => true,
            ],
            'import' => [
                'class'               => 'header_style_manager_import',
                'icon'                => 'theme_import.svg',
                'primary'             => true,
            ],
            'export' => [
                'href'                => 'key=export',
                'class'               => 'header_style_manager_export',
                'icon'                => 'theme_export.svg',
                'primary'             => true,
            ],
            'all',
        ],
        'operations' => [
            'edit',
            'children',
            'copy',
            'delete',
            'show',
        ]
    ],

    // Palettes
    'palettes' => [
        'default'                     => '{title_legend},title,identifier;{config_legend},groupAlias,sorting;desc'
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ],
        'title' => [
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'desc' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'textarea',
            'eval'                    => ['style'=>'height:60px', 'decodeEntities'=>true, 'tl_class'=>'clr'],
            'sql'                     => "text NULL"
        ],
        'identifier' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['mandatory'=>true, 'rgxp'=>'variable', 'nospace'=>true, 'maxlength'=>255, 'tl_class'=>'w50', 'doNotCopy'=>true],
            'sql'                     => "varchar(255) NOT NULL default ''",
        ],
        'groupAlias' => [
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'variable', 'nospace'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "varchar(255) NOT NULL default ''"
        ],
        'sorting' => [
            'exclude'                 => true,
            'sorting'                 => true,
            'inputType'               => 'text',
            'eval'                    => ['rgxp'=>'natural', 'nospace'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'                     => "int(10) NOT NULL default '0'"
        ],
    ]
];
