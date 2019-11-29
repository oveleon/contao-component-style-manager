<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

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
class ComponentStyleSelect extends \Widget
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
        $isEmpty = true;
        $arrCategories = array();
		$objStyleGroups = StyleManagerModel::findByTable($this->strTable, array('order'=>'category'));

		if($objStyleGroups === null)
        {
            return '';
        }

        while($objStyleGroups->next())
        {
            $arrOptions = array();
            $strClass = 'tl_select';
            $arrFieldOptions = array(array('value'=>'', 'label'=>'-'));

            // skip specific content elements
            if(!!$objStyleGroups->extendContentElement && $this->strTable === 'tl_content')
            {
                $arrContentElements = \StringUtil::deserialize($objStyleGroups->contentElements);

                if($arrContentElements !== null && !in_array($this->activeRecord->type, $arrContentElements))
                {
                    continue;
                }
            }

            // skip specific form fields
            if(!!$objStyleGroups->extendFormFields && $this->strTable === 'tl_form_field')
            {
                $arrFormFields = \StringUtil::deserialize($objStyleGroups->formFields);

                if($arrFormFields !== null && !in_array($this->activeRecord->type, $arrFormFields))
                {
                    continue;
                }
            }

            // skip specific modules
            if(!!$objStyleGroups->extendModule && $this->strTable === 'tl_module')
            {
                $arrModules = \StringUtil::deserialize($objStyleGroups->modules);

                if($arrModules !== null && !in_array($this->activeRecord->type, $arrModules))
                {
                    continue;
                }
            }

            $opts = \StringUtil::deserialize($objStyleGroups->cssClasses);

            // prepare data for older versions
            if($opts !== null)
            {
                if(!isset($opts[0]['key']))
                {
                    foreach ($opts as &$item) {
                        $item = array(
                            'key' => $item,
                            'value' => ''
                        );
                    }
                }
            }

            foreach ($opts as $opt) {
                $arrFieldOptions[] = array(
                    'label' => $opt['value'] ?: $opt['key'],
                    'value' => $opt['key']
                );
            }

            // set options
            $strFieldId   = $this->strId . '_' . $objStyleGroups->alias;
            $strFieldName = $this->strName . '[' . $objStyleGroups->alias . ']';

            foreach ($arrFieldOptions as $strKey=>$arrOption)
            {
                if (isset($arrOption['value']))
                {
                    $arrOptions[] = sprintf('<option value="%s"%s>%s</option>',
                        \StringUtil::specialchars($arrOption['value']),
                        $this->isSelected($arrOption),
                        $arrOption['label']);
                }
                else
                {
                    $arrOptgroups = array();

                    foreach ($arrOption as $arrOptgroup)
                    {
                        $arrOptgroups[] = sprintf('<option value="%s"%s>%s</option>',
                            \StringUtil::specialchars($arrOptgroup['value']),
                            $this->isSelected($arrOptgroup),
                            $arrOptgroup['label']);
                    }

                    $arrOptions[] = sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', \StringUtil::specialchars($strKey), implode('', $arrOptgroups));
                }
            }

            // add chosen
            if(!!$objStyleGroups->chosen)
            {
                $strClass .= ' tl_chosen';
            }

            // set categories
            $categoryAlias = $objStyleGroups->category ? \StringUtil::generateAlias($objStyleGroups->category) : 'no_category';

            if(!in_array($categoryAlias, array_keys($arrCategories)))
            {
                $arrCategories[ $categoryAlias ] = array(
                    'label'  => $objStyleGroups->category,
                    'fields' => array()
                );
            }

            $arrCategories[ $categoryAlias ]['fields'][] = sprintf('%s<select name="%s" id="ctrl_%s" class="%s%s"%s onfocus="Backend.getScrollOffset()">%s</select>%s%s',
                '<div><h3><label>' . $objStyleGroups->title . '</label></h3>',
                $strFieldName,
                $strFieldId,
                $strClass,
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                $this->getAttributes(),
                implode('', $arrOptions),
                $this->wizard,
                '<p class="tl_help tl_tip" title="">'.$objStyleGroups->description.'</p></div>'
            );

            $isEmpty = false;
        }

		if($isEmpty)
		{
		    \System::loadLanguageFile('tl_style_manager');
		    return '<div class="no_styles tl_info"><p>' . $GLOBALS['TL_LANG']['tl_style_manager']['noStylesDefined'] . '</p></div>';
        }

        $arrFieldsets = array();

        foreach ($arrCategories as $alias => $category)
        {
            $label = $category['label'];

            $arrFieldsets[] = sprintf('<fieldset%s>%s%s</fieldset>',
                $label ? ' class="legend"' : '',
                $label ? '<legend>' . $label . '</legend>' : '',
                implode("",$category['fields'])
            );
        }

		return implode("",$arrFieldsets);
	}
}
