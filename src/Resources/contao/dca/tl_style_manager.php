<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
*/

$GLOBALS['TL_DCA']['tl_style_manager'] = array
(
    // Config
    'config' => array
    (
        'dataContainer'               => 'Table',
        'ptable'                      => 'tl_style_manager_archive',
        'switchToEdit'                => true,
        'enableVersioning'            => true,
        'markAsCopy'                  => 'title',
        'onload_callback' => array
        (
            array('tl_style_manager', 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid,sorting' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode'                    => 4,
            'fields'                  => array('sorting'),
            'headerFields'            => array('title', 'identifier'),
            'panelLayout'             => 'filter;sort,search,limit',
            'disableGrouping'         => true,
            'child_record_callback'   => array('tl_style_manager', 'listGroupRecords'),
            'child_record_class'      => 'no_padding'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'editheader' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['editheader'],
                'href'                => 'act=edit',
                'icon'                => 'edit.svg'
            ),
            'copy' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['copy'],
                'href'                => 'act=paste&mode=copy',
                'icon'                => 'copy.svg'
            ),
            'delete' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['delete'],
                'href'                => 'act=delete',
                'icon'                => 'delete.svg',
                'attributes'          => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['tl_style_manager']['show'],
                'href'                => 'act=show',
                'icon'                => 'show.svg'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        '__selector__'                => array('extendContentElement','extendFormFields','extendModule'),
        'default'                     => '{title_legend},title,alias,description;{config_legend},cssClasses;{publish_legend},extendLayout,extendPage,extendArticle,extendModule,extendNews,extendEvents,extendForm,extendFormFields,extendContentElement;{expert_legend:hide},chosen,passToTemplate;'
    ),

    // Sub-Palettes
    'subpalettes' => array
    (
        'extendContentElement'        => 'contentElements',
        'extendFormFields'            => 'formFields',
        'extendModule'                => 'modules'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'foreignKey'              => 'tl_style_manager_archive.title',
            'sql'                     => "int(10) unsigned NOT NULL default 0",
            'relation'                => array('type'=>'belongsTo', 'load'=>'lazy')
        ),
        'sorting' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default 0"
        ),
        'tstamp' => array
        (
            'sql'                     => "int(10) unsigned NOT NULL default '0'"
        ),
        'alias' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['alias'],
            'inputType'               => 'text',
            'search'                  => true,
            'eval'                    => array('rgxp'=>'alias', 'maxlength'=>128, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) COLLATE utf8_bin NOT NULL default ''",
            'save_callback' => array
            (
                array('tl_style_manager', 'generateAlias')
            ),
        ),
        'title' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['title'],
            'exclude'                 => true,
            'search'                  => true,
            'inputType'               => 'text',
            'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['description'],
            'inputType'               => 'text',
            'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
            'sql'                     => "varchar(255) NOT NULL default ''"
        ),
        'cssClasses' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['cssClasses'],
            'exclude'                 => true,
            'inputType'               => 'keyValueWizard',
            'eval'                    => array('allowHtml'=>true, 'tl_class'=>'clr long'),
            'load_callback'           => array(
                array('tl_style_manager', 'prepareData'),
                array('tl_style_manager', 'translateKeyValue')
            ),
            'sql'                     => "blob NULL"
        ),
        'chosen' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['chosen'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default '1'"
        ),
        'passToTemplate' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['passToTemplate'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 m12'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendLayout' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendLayout'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendPage' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendPage'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendArticle' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendArticle'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendForm' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendForm'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendFormFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendFormFields'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr', 'submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'formFields' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['formFields'],
            'inputType'               => 'checkbox',
            'options_callback'        => array('tl_style_manager', 'getFormFields'),
            'eval'                    => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL"
        ),
        'extendContentElement' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendContentElement'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr', 'submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'contentElements' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['contentElements'],
            'inputType'               => 'checkbox',
            'options_callback'        => array('tl_style_manager', 'getContentElements'),
            'reference'               => &$GLOBALS['TL_LANG']['CTE'],
            'eval'                    => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL"
        ),
        'extendModule' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendModule'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr', 'submitOnChange'=>true),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'modules' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['modules'],
            'inputType'               => 'checkbox',
            'options_callback'        => array('tl_style_manager', 'getModules'),
            'reference'               => &$GLOBALS['TL_LANG']['FMD'],
            'eval'                    => array('multiple'=>true, 'mandatory'=>true, 'tl_class'=>'w50 clr'),
            'sql'                     => "blob NULL"
        ),
        'extendNews' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendNews'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        ),
        'extendEvents' => array
        (
            'label'                   => &$GLOBALS['TL_LANG']['tl_style_manager']['extendEvents'],
            'exclude'                 => true,
            'filter'                  => true,
            'inputType'               => 'checkbox',
            'eval'                    => array('tl_class'=>'w50 clr'),
            'sql'                     => "char(1) NOT NULL default ''"
        )
    )
);


/**
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
 */

use Oveleon\ContaoComponentStyleManager\StyleManagerModel;

class tl_style_manager extends \Backend
{
    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * Check permissions to edit table tl_style_manager
     *
     * @throws Contao\CoreBundle\Exception\AccessDeniedException
     */
    public function checkPermission()
    {
        $bundles = Contao\System::getContainer()->getParameter('kernel.bundles');

        if (!isset($bundles['ContaoCalendarBundle']))
        {
            unset($GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendEvents']);
        }

        if (!isset($bundles['ContaoNewsBundle']))
        {
            unset($GLOBALS['TL_DCA']['tl_style_manager']['fields']['extendNews']);
        }

        return;
    }

    /**
     * List a group record
     *
     * @param array $row
     *
     * @return string
     */
    public function listGroupRecords($row)
    {
        $arrExtends = null;
        $label = $row['title'];

        if($row['passToTemplate'])
        {
            $label = '<span class="sm_list_token var" title="$this->styleManager->get(string: identifier [, array: ' . $row['alias'] . '])">$</span> ' . $label;
        }
        else
        {
            $label = '<span class="sm_list_token">C</span> ' . $label;
        }

        foreach ($row as $field => $value)
        {
            if(strpos($field, 'extend') === 0 && !!$value)
            {
                $arrExtends[] = &$GLOBALS['TL_LANG']['tl_style_manager'][ $field ][0];
            }
        }

        if($arrExtends !== null)
        {
            $label .= '<span style="color:#999;padding-left:3px">[' . implode(", ", $arrExtends) . ']</span>';
        }

        return $label;
    }

    /**
     * Auto-generate an style group alias if it has not been set yet
     *
     * @param mixed          $varValue
     * @param \DataContainer $dc
     *
     * @return string
     *
     * @throws Exception
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            return $this->Database->prepare("SELECT id FROM tl_style_manager WHERE alias=? AND id!=? AND pid=?")->execute($alias, $dc->id, $dc->activeRecord->pid)->numRows > 0;
        };

        // Generate an alias if there is none
        if ($varValue == '')
        {
            $varValue = Contao\System::getContainer()->get('contao.slug')->generate($dc->activeRecord->title, $dc->id, $aliasExists);
        }
        elseif ($aliasExists($varValue))
        {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['groupAliasExists'], $varValue));
        }

        return $varValue;
    }

    /**
     * Prepare data from older versions
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function translateKeyValue($varValue, \DataContainer $dc)
    {
        $GLOBALS['TL_LANG']['MSC']['ow_key'] = $GLOBALS['TL_LANG']['tl_style_manager']['ow_key'];
        $GLOBALS['TL_LANG']['MSC']['ow_value'] = $GLOBALS['TL_LANG']['tl_style_manager']['ow_value'];

        return $varValue;
    }

    /**
     * Prepare data from older versions
     *
     * @param mixed         $varValue
     * @param \DataContainer $dc
     *
     * @return string
     */
    public function prepareData($varValue, \DataContainer $dc)
    {
        $arrValue = \StringUtil::deserialize($varValue);

        if($arrValue !== null)
        {
            if(!isset($arrValue[0]['key']))
            {
                foreach ($arrValue as &$item) {
                    $item = array(
                        'key' => $item,
                        'value' => ''
                    );
                }
            }
        }

        return serialize($arrValue);
    }

    /**
     * Return all content elements as array
     *
     * @return array
     */
    public function getContentElements()
    {
        $groups = array();

        foreach ($GLOBALS['TL_CTE'] as $k=>$v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }

    /**
     * Return all form fields as array
     *
     * @return array
     */
    public function getFormFields()
    {
        \System::loadLanguageFile('tl_form_field');

        $arrFields = $GLOBALS['TL_FFL'];

        // Add the translation
        foreach (array_keys($arrFields) as $key)
        {
            $arrFields[$key] = $GLOBALS['TL_LANG']['FFL'][$key][0];
        }

        return $arrFields;
    }

    /**
     * Get all modules and return them as array
     *
     * @return array
     */
    public function getModules()
    {
        $groups = array();

        foreach ($GLOBALS['FE_MOD'] as $k=>$v)
        {
            foreach (array_keys($v) as $kk)
            {
                $groups[$k][] = $kk;
            }
        }

        return $groups;
    }
}
