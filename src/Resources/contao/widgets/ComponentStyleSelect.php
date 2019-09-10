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
        $arrFields = array();
        $strClass = 'tl_select';
		$objStyleGroups = StyleManagerModel::findByTable($this->strTable);

		if($objStyleGroups === null)
        {
            return '';
        }

        while($objStyleGroups->next())
        {
            $arrFieldOptions = array(array('value'=>'', 'label'=>'-'));
            $arrOptions = array();
            $opts = \StringUtil::deserialize($objStyleGroups->cssClasses);

            foreach ($opts as $opt) {
                $arrFieldOptions[] = array(
                    'label' => $opt,
                    'value' => $opt
                );
            }

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

            // Add Chosen
            $strClass .= ' tl_chosen';

            $arrFields[] = sprintf('%s<select name="%s" id="ctrl_%s" class="%s%s"%s onfocus="Backend.getScrollOffset()">%s</select>%s%s',
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
        }

        return implode("",$arrFields);
	}
}
