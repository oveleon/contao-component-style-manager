<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Widget;

use Contao\BackendUser;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Database;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provide methods to handle select menus for style manager.
 *
 * @property bool  $mandatory
 * @property int   $size
 * @property bool  $multiple
 * @property array $options
 * @property bool  $chosen
 */
class ComponentStyleSelect extends Widget
{
    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    private readonly ContaoCsrfTokenManager $tokenManager;

    private readonly InsertTagParser $insertTagParser;

    private readonly RequestStack $requestStack;

    private readonly bool $showGroupTitle;

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $container = System::getContainer();

        $this->tokenManager = $container->get('contao.csrf.token_manager');
        $this->insertTagParser = $container->get('contao.insert_tag.parser');
        $this->requestStack = $container->get('request_stack');

        $this->showGroupTitle = (bool) $container->getParameter('contao_component_style_manager.show_group_title');
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $arrObjStyleArchives = StyleManagerArchiveModel::findAllWithConfiguration(array('order'=>'sorting'));
        $arrObjStyleGroups   = StyleManagerModel::findByTableAndConfiguration($this->strTable, array('order'=>'pid,sorting'));

        if($arrObjStyleGroups === null || $arrObjStyleArchives === null)
        {
            return $this->renderEmptyMessage();
        }

        $isEmpty       = true;
        $arrCollection = array();
        $arrArchives   = array();
        $arrOrder      = array();

        // Prepare archives
        foreach($arrObjStyleArchives as $objStyleArchive)
        {
            $arrArchives[ $objStyleArchive->id ] = array(
                'title'      => $objStyleArchive->title,
                'identifier' => $objStyleArchive->identifier,
                'desc'       => $objStyleArchive->desc,
                'group'      => $objStyleArchive->groupAlias,
                'model'      => $objStyleArchive
            );

            $arrOrder[] = $objStyleArchive->identifier;
        }

        // Restore default values
        $this->varValue = StyleManager::deserializeValues($this->varValue);

        // Prepare group fields
        foreach($arrObjStyleGroups as $objStyleGroup)
        {
            $arrOptions      = array();
            $strClass        = 'tl_select';
            $arrFieldOptions = array();

            // set blank option
            if(!!$objStyleGroup->blankOption)
            {
                $arrFieldOptions[] = array('value'=>'', 'label'=>'-');
            }

            // skip specific content elements
            if(!!$objStyleGroup->extendContentElement && $this->strTable === 'tl_content')
            {
                $arrContentElements = StringUtil::deserialize($objStyleGroup->contentElements);

                if($arrContentElements !== null && !in_array($this->activeRecord->type, $arrContentElements))
                {
                    continue;
                }
            }

            // skip specific form fields
            if(!!$objStyleGroup->extendFormFields && $this->strTable === 'tl_form_field')
            {
                $arrFormFields = StringUtil::deserialize($objStyleGroup->formFields);

                if($arrFormFields !== null && !in_array($this->activeRecord->type, $arrFormFields))
                {
                    continue;
                }
            }

            // skip specific modules
            if(!!$objStyleGroup->extendModule && $this->strTable === 'tl_module')
            {
                $arrModules = StringUtil::deserialize($objStyleGroup->modules);

                if($arrModules !== null && !in_array($this->activeRecord->type, $arrModules))
                {
                    continue;
                }
            }

            // skip third-party fields
            if (isset($GLOBALS['TL_HOOKS']['styleManagerSkipField']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerSkipField']))
            {
                foreach ($GLOBALS['TL_HOOKS']['styleManagerSkipField'] as $callback)
                {
                    if(System::importStatic($callback[0])->{$callback[1]}($objStyleGroup, $this))
                    {
                        continue 2;
                    }
                }
            }

            $opts = StringUtil::deserialize($objStyleGroup->cssClasses);

            foreach ($opts as $opt) {
                $arrFieldOptions[] = array(
                    'label' => $opt['value'] ?: $opt['key'],
                    'value' => $opt['key']
                );
            }

            // dynamically change or expand group options
            if (isset($GLOBALS['TL_HOOKS']['styleManagerGroupFieldOptions']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerGroupFieldOptions']))
            {
                foreach ($GLOBALS['TL_HOOKS']['styleManagerGroupFieldOptions'] as $callback)
                {
                    if($optionCallback = System::importStatic($callback[0])->{$callback[1]}($arrFieldOptions, $objStyleGroup, $this))
                    {
                        $arrFieldOptions = $optionCallback;
                    }
                }
            }

            $strId        = StyleManager::generateAlias($arrArchives[ $objStyleGroup->pid ]['identifier'], $objStyleGroup->alias);
            $strFieldId   = $this->strId . '_' . $strId;
            $strFieldName = $this->strName . '[' . $strId . ']';

            foreach ($arrFieldOptions as $strKey=>$arrOption)
            {
                if (isset($arrOption['value']))
                {
                    $arrOptions[] = sprintf('<option value="%s"%s>%s</option>',
                        StringUtil::specialchars($arrOption['value']),
                        static::optionSelected($arrOption['value'], $this->varValue[ $strId ] ?? ''),
                        $arrOption['label']);
                }
                else
                {
                    $arrOptgroups = array();

                    foreach ($arrOption as $arrOptgroup)
                    {
                        $arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
                            StringUtil::specialchars($arrOptgroup['value']),
                            static::optionSelected($arrOption['value'], $this->varValue[ $strId ] ?? ''),
                            $arrOptgroup['label']);
                    }

                    $arrOptions[] = sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', StringUtil::specialchars($strKey), implode('', $arrOptgroups));
                }
            }

            // add chosen
            if(!!$objStyleGroup->chosen)
            {
                $strClass .= ' tl_chosen';
            }

            // create collection
            $groupAlias      = ($arrArchives[ $objStyleGroup->pid ]['group'] ?: 'group-' . $arrArchives[ $objStyleGroup->pid ]['identifier']) . '-' . $this->id;
            $collectionAlias = $arrArchives[ $objStyleGroup->pid ]['identifier'];

            if(!in_array($collectionAlias, array_keys($arrCollection)))
            {
                $arrCollection[ $collectionAlias ] = array(
                    'label'      => $arrArchives[ $objStyleGroup->pid ]['title'],
                    'desc'       => $arrArchives[ $objStyleGroup->pid ]['desc'],
                    'group'      => $groupAlias,
                    'groupTitle' => $arrArchives[ $objStyleGroup->pid ]['group'] ?? null,
                    'fields'     => array()
                );
            }

            $arrCollection[ $collectionAlias ]['fields'][] = sprintf('%s<select name="%s" id="ctrl_%s" class="%s%s"%s onfocus="Backend.getScrollOffset()">%s</select>%s%s',
                ($objStyleGroup->cssClass === 'seperator' || $objStyleGroup->cssClass === 'separator' ? '<hr>' : '') . '<div' . ($objStyleGroup->cssClass ? ' class="' . $objStyleGroup->cssClass . '"' : '').'><h3><label>' . $objStyleGroup->title . '</label></h3>',
                $strFieldName,
                $strFieldId,
                $strClass,
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                $this->getAttributes(),
                implode('', $arrOptions),
                $this->wizard,
                '<p class="tl_help' . ($objStyleGroup->description ? ' tl_tip' : '') . '" title="">'.$objStyleGroup->description.'</p></div>'
            );

            $isEmpty = false;
        }

        if($isEmpty)
        {
            return $this->renderEmptyMessage();
        }

        $objSession = $this->requestStack->getSession()->getBag('contao_backend');
        $arrSession = $objSession->get('stylemanager_section_states');

        $arrGroups   = array();
        $arrSections = array();

        // sort collection by sort-index
        uksort($arrCollection, function($key1, $key2) use ($arrOrder) {
            return (array_search($key1, $arrOrder) > array_search($key2, $arrOrder));
        });

        // collect groups
        foreach ($arrCollection as $alias => $collection)
        {
            $arrGroups[ $collection['group'] ][ $alias ] = $collection;
        }

        // create group tabs and content
        foreach ($arrGroups as $groupAlias => $groups)
        {
            $arrNavigation = array();
            $arrContent = array();
            $groupTitle = null;

            $i = 0;

            foreach ($groups as $key => $group)
            {
                $identifier = sprintf('%s-%s-%s', $i, $key, $this->id);
                $sessionAlias = $arrSession[ $groupAlias ] ?? null;
                $isSelected = !isset($sessionAlias) && $i===0 ? 'checked' : ($sessionAlias === $identifier ? 'checked' : '');
                $index      = $isSelected ?: $i;

                $onClick = sprintf('onclick="Backend.getScrollOffset(); new Request.Contao().post({\'action\':\'selectStyleManagerSection\', \'id\':\'%s\', \'groupAlias\':\'%s\', \'identifier\':\'%s\', \'REQUEST_TOKEN\':\'%s\'});"',
                    $this->id,
                    $groupAlias,
                    $identifier,
                    $this->tokenManager->getDefaultTokenValue()
                );

                $arrNavigation[ $index ] = sprintf('<input type="radio" id="nav-%s" class="tab-nav" name="nav-%s" %s><label for="nav-%s" %s>%s</label>',
                    $identifier,
                    $groupAlias,
                    $isSelected,
                    $identifier,
                    $onClick,
                    $group['label']
                );

                $arrContent[ $index ] = sprintf('<div id="tab-%s" class="tab-content">%s%s</div>',
                    $identifier,
                    (trim($group['desc']) ? '<div class="long desc">' . $this->insertTagParser->replaceInline(nl2br($group['desc'])) . '</div>' : ''),
                    implode("", $group['fields'])
                );

                // Set group title if it exists
                if ($this->showGroupTitle && null !== $group['groupTitle'])
                {
                    $groupTitle = '<h4 class="sm-groupTitle">' . $group['groupTitle'] . '</h4>';
                }

                $i++;
            }

            // if no entry is selected, the first one must be selected
            if(!array_key_exists('checked', $arrNavigation))
            {
                $arrNavigation[0] = str_replace("><label", "checked><label", $arrNavigation[0]);
            }

            $arrSections[] = '<div class="tab-container" id="' . $groupAlias . '">' . $groupTitle . implode("", $arrNavigation) . implode("", $arrContent) . '</div>';
        }

        return implode("", $arrSections);
    }

    /**
     * Return the empty message
     *
     * @return string
     */
    private function renderEmptyMessage()
    {
        System::loadLanguageFile('tl_style_manager');
        return '<div class="no_styles tl_info"><p>' . $GLOBALS['TL_LANG']['tl_style_manager']['noStylesDefined'] . '</p></div>';
    }

    /**
     * Check for a valid option and prepare template variables
     */
    public function validate()
    {
        $this->varValue = $this->getPost($this->strName);

        if($this->varValue === null)
        {
            return;
        }

        if($arrValue = StyleManager::serializeValues($this->varValue, $this->strTable))
        {
            $this->varValue = $arrValue;
        }

        $field   = StyleManager::getClassFieldNameByTable($this->strTable);
        $objUser = BackendUser::getInstance();

        // Update css class fields in case of multiple editing or if a user has no rights for the field
        if ($field && (Input::get('act') === 'editAll' || !$objUser->hasAccess($this->strTable . '::' . $field, 'alexf')))
        {
            $stdClass = new \stdClass();
            $stdClass->field = $field;
            $stdClass->table = $this->strTable;

            $stdClass->activeRecord = new \stdClass();
            $stdClass->activeRecord->styleManager = $this->varValue;

            $value = StyleManager::resetClasses($this->activeRecord->{$field}, $stdClass, $this->strTable);
            $value = StyleManager::updateClasses($value, $stdClass);

            // Update css class field
            Database::getInstance()->prepare('UPDATE ' . $this->strTable . ' SET ' . $field . '=? WHERE id=?')
                ->execute($value, $this->activeRecord->id);
        }
    }
}
