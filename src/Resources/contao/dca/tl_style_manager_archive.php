<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
*/

$GLOBALS['TL_DCA']['tl_style_manager_archive'] = [
    // Config
    'config' => [
        'dataContainer'               => 'Table',
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
            'mode'                    => 1,
            'fields'                  => ['groupAlias', 'sorting'],
            'panelLayout'             => 'filter;search,limit'
        ],
        'label' => [
            'fields'                  => ['title'],
            'format'                  => '%s'
        ],
        'global_operations' => [
            'import' => [
                'href'                => 'key=import',
                'class'               => 'header_style_manager_import',
                'icon'                => 'theme_import.svg'
            ],
            'export' => [
                'href'                => 'key=export',
                'class'               => 'header_style_manager_export',
                'icon'                => 'theme_export.svg'
            ],
            'all' => [
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ],
            'config' => [
                'href'                => 'key=import',
                'class'               => 'header_style_manager_config'
            ]
        ],
        'operations' => [
            'edit' => [
                'href'                => 'table=tl_style_manager',
                'icon'                => 'edit.svg'
            ],
            'editheader' => [
                'href'                => 'act=edit',
                'icon'                => 'header.svg',
            ],
            'copy' => [
                'href'                => 'act=copy',
                'icon'                => 'copy.svg'
            ],
            'delete' => [
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            ]
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
        ]
    ]
];
