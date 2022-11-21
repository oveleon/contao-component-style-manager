<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
*/

$GLOBALS['TL_DCA']['tl_style_manager'] = [

    // Config
    'config' => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_style_manager_archive',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'markAsCopy'       => 'title',
        'sql' => [
            'keys' => [
                'id'          => 'primary',
                'pid,sorting' => 'index'
            ]
        ]
    ],

    // List
    'list' => [
        'sorting' => [
            'mode'               => 4,
            'fields'             => ['sorting'],
            'headerFields'       => ['title', 'identifier'],
            'panelLayout'        => 'filter;sort,search,limit',
            'disableGrouping'    => true,
            'child_record_class' => 'no_padding'
        ],
        'global_operations' => [
            'all' => [
                'href'          => 'act=select',
                'class'         => 'header_edit_all',
                'attributes'    => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ],
        'operations' => [
            'editheader' => [
                'href'          => 'act=edit',
                'icon'          => 'edit.svg'
            ],
            'copy' => [
                'href'          => 'act=paste&mode=copy',
                'icon'          => 'copy.svg'
            ],
            'delete' => [
                'href'          => 'act=delete',
                'icon'          => 'delete.svg',
                'attributes'    => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'href'          => 'act=show',
                'icon'          => 'show.svg'
            ]
        ]
    ],

    // Palettes
    'palettes' => [
        '__selector__'         => ['extendContentElement','extendFormFields','extendModule'],
        'default'              => '{title_legend},title,alias,description;{config_legend},cssClasses;{publish_legend},extendLayout,extendPage,extendArticle,extendModule,extendNews,extendEvents,extendForm,extendFormFields,extendContentElement;{expert_legend:hide},chosen,blankOption,cssClass,passToTemplate;'
    ],

    // Sub-Palettes
    'subpalettes' => [
        'extendContentElement' => 'contentElements',
        'extendFormFields'     => 'formFields',
        'extendModule'         => 'modules'
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql'            => "int(10) unsigned NOT NULL auto_increment"
        ],
        'pid' => [
            'foreignKey'     => 'tl_style_manager_archive.title',
            'sql'            => "int(10) unsigned NOT NULL default 0",
            'relation'       => ['type'=>'belongsTo', 'load'=>'lazy']
        ],
        'sorting' => [
            'sql'            => "int(10) unsigned NOT NULL default 0"
        ],
        'tstamp' => [
            'sql'            => "int(10) unsigned NOT NULL default '0'"
        ],
        'alias' => [
            'inputType'      => 'text',
            'search'         => true,
            'eval'           => ['rgxp'=>'alias', 'maxlength'=>128, 'tl_class'=>'w50'],
            'sql'            => "varchar(255) BINARY NOT NULL default ''"
        ],
        'title' => [
            'exclude'        => true,
            'search'         => true,
            'inputType'      => 'text',
            'eval'           => ['mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
            'sql'            => "varchar(255) NOT NULL default ''"
        ],
        'description' => [
            'inputType'      => 'text',
            'eval'           => ['maxlength'=>255, 'tl_class'=>'w50'],
            'sql'            => "varchar(255) NOT NULL default ''"
        ],
        'cssClasses' => [
            'exclude'        => true,
            'inputType'      => 'keyValueWizard',
            'eval'           => ['allowHtml'=>true, 'tl_class'=>'clr long'],
            'sql'            => "blob NULL"
        ],
        'cssClass' => [
            'exclude'        => true,
            'inputType'      => 'text',
            'reference'      => &$GLOBALS['TL_LANG']['tl_style_manager'],
            'eval'           => ['helpwizard'=>true, 'maxlength'=>64, 'tl_class'=>'w50'],
            'explanation'    => 'styleManagerFieldClass',
            'sql'            => "varchar(64) NOT NULL default ''"
        ],
        'chosen' => [
            'exclude'        => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50'],
            'sql'            => "char(1) NOT NULL default '1'"
        ],
        'blankOption' => [
            'exclude'        => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50'],
            'sql'            => "char(1) NOT NULL default '1'"
        ],
        'passToTemplate' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 m12'],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'extendLayout' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr'],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'extendPage' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr'],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'extendArticle' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr'],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'extendForm' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr'],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'extendFormFields' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr', 'submitOnChange'=>true],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'formFields' => [
            'inputType'      => 'checkbox',
            'eval'           => ['multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'],
            'sql'            => "blob NULL"
        ],
        'extendContentElement' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr', 'submitOnChange'=>true],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'contentElements' => [
            'inputType'      => 'checkbox',
            'reference'      => &$GLOBALS['TL_LANG']['CTE'],
            'eval'           => ['multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'],
            'sql'            => "blob NULL"
        ],
        'extendModule' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr', 'submitOnChange'=>true],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'modules' => [
            'inputType'      => 'checkbox',
            'reference'      => &$GLOBALS['TL_LANG']['FMD'],
            'eval'           => ['multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'],
            'sql'            => "blob NULL"
        ],
        'extendNews' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr'],
            'sql'            => "char(1) NOT NULL default ''"
        ],
        'extendEvents' => [
            'exclude'        => true,
            'filter'         => true,
            'inputType'      => 'checkbox',
            'eval'           => ['tl_class'=>'w50 clr'],
            'sql'            => "char(1) NOT NULL default ''"
        ]
    ]
];
