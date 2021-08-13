<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

use Contao\BackendUser;
use Contao\Database;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;

/**
 * Provide methods to handle select menus for style manager.
 *
 * @property boolean $mandatory
 * @property integer $size
 * @property boolean $multiple
 * @property array   $options
 * @property boolean $chosen
 *
 * @author Daniele Sciannimanica <daniele@oveleon.de>
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

	/**
	 * Generate the widget and return it as string
	 *
	 * @return string
	 */
	public function generate()
	{
        $objStyleArchives = StyleManagerArchiveModel::findAll(array('order'=>'sorting'));
		$objStyleGroups   = StyleManagerModel::findByTable($this->strTable, array('order'=>'pid,sorting'));

		if($objStyleGroups === null || $objStyleArchives === null)
        {
            return $this->renderEmptyMessage();
        }

        $isEmpty       = true;
        $arrCollection = array();
        $arrArchives   = array();
        $arrOrder      = array();

        // Prepare archives
		while($objStyleArchives->next())
        {
            $arrArchives[ $objStyleArchives->id ] = array(
                'title'      => $objStyleArchives->title,
                'identifier' => $objStyleArchives->identifier,
                'desc'       => $objStyleArchives->desc,
                'group'      => $objStyleArchives->groupAlias,
                'model'      => $objStyleArchives->current()
            );

            $arrOrder[] = $objStyleArchives->identifier;
        }

        // Restore default values
        $this->varValue = StyleManager::deserializeValues($this->varValue);

        // Prepare group fields
        while($objStyleGroups->next())
        {
            $arrOptions      = array();
            $strClass        = 'tl_select';
            $arrFieldOptions = array();

            // set blank option
            if(!!$objStyleGroups->blankOption)
            {
                $arrFieldOptions[] = array('value'=>'', 'label'=>'-');
            }

            // skip specific content elements
            if(!!$objStyleGroups->extendContentElement && $this->strTable === 'tl_content')
            {
                $arrContentElements = StringUtil::deserialize($objStyleGroups->contentElements);

                if($arrContentElements !== null && !in_array($this->activeRecord->type, $arrContentElements))
                {
                    continue;
                }
            }

            // skip specific form fields
            if(!!$objStyleGroups->extendFormFields && $this->strTable === 'tl_form_field')
            {
                $arrFormFields = StringUtil::deserialize($objStyleGroups->formFields);

                if($arrFormFields !== null && !in_array($this->activeRecord->type, $arrFormFields))
                {
                    continue;
                }
            }

            // skip specific modules
            if(!!$objStyleGroups->extendModule && $this->strTable === 'tl_module')
            {
                $arrModules = StringUtil::deserialize($objStyleGroups->modules);

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
                    if(System::importStatic($callback[0])->{$callback[1]}($objStyleGroups, $this))
                    {
                        continue 2;
                    }
                }
            }

            $opts = StringUtil::deserialize($objStyleGroups->cssClasses);

            foreach ($opts as $opt) {
                $arrFieldOptions[] = array(
                    'label' => $opt['value'] ?: $opt['key'],
                    'value' => $opt['key']
                );
            }

            // set options
            $strFieldId   = $this->strId . '_' . $objStyleGroups->id;
            $strFieldName = $this->strName . '[' . $objStyleGroups->id . ']';

            foreach ($arrFieldOptions as $strKey=>$arrOption)
            {
                if (isset($arrOption['value']))
                {
                    $arrOptions[] = sprintf('<option value="%s"%s>%s</option>',
                        StringUtil::specialchars($arrOption['value']),

                        // @deprecated: to be removed in Version 3.0. (interception of storage based on the alias. In future, only the ID must be set)
                        static::optionSelected($arrOption['value'], $this->varValue[ $objStyleGroups->id ] ?? '') ?: static::optionSelected($arrOption['value'], $this->varValue[ $objStyleGroups->alias ] ?? ''),

                        $arrOption['label']);
                }
                else
                {
                    $arrOptgroups = array();

                    foreach ($arrOption as $arrOptgroup)
                    {
                        $arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
                            StringUtil::specialchars($arrOptgroup['value']),

                            // @deprecated: to be removed in Version 3.0. (interception of storage based on the alias. In future, only the ID must be set)
                            static::optionSelected($arrOption['value'], $this->varValue[ $objStyleGroups->id ] ?? '') ?: static::optionSelected($arrOption['value'], $this->varValue[ $objStyleGroups->alias ] ?? ''),

                            $arrOptgroup['label']);
                    }

                    $arrOptions[] = sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', StringUtil::specialchars($strKey), implode('', $arrOptgroups));
                }
            }

            // add chosen
            if(!!$objStyleGroups->chosen)
            {
                $strClass .= ' tl_chosen';
            }

            // create collection
            $groupAlias      = ($arrArchives[ $objStyleGroups->pid ]['group'] ?: 'group-' . $arrArchives[ $objStyleGroups->pid ]['identifier']) . '-' . $this->id;
            $collectionAlias = $arrArchives[ $objStyleGroups->pid ]['identifier'];

            if(!in_array($collectionAlias, array_keys($arrCollection)))
            {
                $arrCollection[ $collectionAlias ] = array(
                    'label'  => $arrArchives[ $objStyleGroups->pid ]['title'],
                    'desc'   => $arrArchives[ $objStyleGroups->pid ]['desc'],
                    'group'  => $groupAlias,
                    'fields' => array()
                );
            }

            $arrCollection[ $collectionAlias ]['fields'][] = sprintf('%s<select name="%s" id="ctrl_%s" class="%s%s"%s onfocus="Backend.getScrollOffset()">%s</select>%s%s',
                ($objStyleGroups->cssClass === 'seperator' || $objStyleGroups->cssClass === 'separator' ? '<hr>' : '') . '<div' . ($objStyleGroups->cssClass ? ' class="' . $objStyleGroups->cssClass . '"' : '').'><h3><label>' . $objStyleGroups->title . '</label></h3>',
                $strFieldName,
                $strFieldId,
                $strClass,
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                $this->getAttributes(),
                implode('', $arrOptions),
                $this->wizard,
                '<p class="tl_help' . ($objStyleGroups->description ? ' tl_tip' : '') . '" title="">'.$objStyleGroups->description.'</p></div>'
            );

            $isEmpty = false;
        }

		if($isEmpty)
		{
            return $this->renderEmptyMessage();
        }

        $objSession = System::getContainer()->get('session')->getBag('contao_backend');
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

            $i = 0;

            foreach ($groups as $key => $group)
            {
                $identifier = sprintf('%s-%s-%s', $i, $key, $this->id);
                $isSelected = !isset($arrSession[ $groupAlias ]) && $i===0 ? 'checked' : ($arrSession[ $groupAlias ] === $identifier ? 'checked' : '');
                $index      = $isSelected ?: $i;

                $onClick = sprintf('onclick="Backend.getScrollOffset(); new Request.Contao().post({\'action\':\'selectStyleManagerSection\', \'id\':\'%s\', \'groupAlias\':\'%s\', \'identifier\':\'%s\', \'REQUEST_TOKEN\':\'%s\'});"',
                    $this->id,
                    $groupAlias,
                    $identifier,
                    REQUEST_TOKEN
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
                    (trim($group['desc']) ? '<div class="long desc">' . $this->replaceInsertTags(nl2br($group['desc'])) . '</div>' : ''),
                    implode("", $group['fields'])
                );

                $i++;
            }

            // if no entry is selected, the first one must be selected
            if(!array_key_exists('checked', $arrNavigation))
            {
                $arrNavigation[0] = str_replace("><label", "checked><label", $arrNavigation[0]);
            }

            $arrSections[] = '<div class="tab-container" id="' . $groupAlias . '">' . implode("", $arrNavigation) . implode("", $arrContent) . '</div>';
        }

		return implode("", $arrSections);
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
}
