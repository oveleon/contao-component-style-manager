<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

class StyleManager
{
    /**
     * Valid CSS-Class fields
     *
     * field => size
     *
     * @var array
     */
    public $validCssClassFields = array(
        'cssID'      => 2,
        'cssClass'   => 1,
        'class'      => 1,
        'attributes' => 2
    );

    /**
     * Clear StyleManager classes from css class field
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function clearStyleManager($varValue, $dc)
    {
        if($this->isMultipleField($dc->field))
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $arrValues = \StringUtil::deserialize($dc->activeRecord->styleManager, true);

        // remove non-exiting values
        $this->cleanupClasses($arrValues, $dc->table);

        if(count($arrValues))
        {
            foreach ($arrValues as $k => $v)
            {
                $arrValues[ $k ] = ' ' . $v . ' ';
            }

            $varValue = ' ' . $varValue . ' ';
            $varValue = str_replace($arrValues, ' ', $varValue);
            $varValue = trim(preg_replace('#\s+#', ' ', $varValue));
        }

        if($this->isMultipleField($dc->field))
        {
            $varValue = serialize(array($cssID[0], $varValue));
        }

        return $varValue;
    }

    /**
     * Update StyleManager classes
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function updateStyleManager($varValue, $dc)
    {
        if($this->isMultipleField($dc->field))
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $varValues = \StringUtil::deserialize($dc->activeRecord->styleManager, true);
        $varValues = array_filter($varValues);
        $varValue .= ($varValue ? ' ' : '') . (count($varValues) ? implode(' ', $varValues) : '');
        $varValue  = trim($varValue);

        if($this->isMultipleField($dc->field))
        {
            $varValue = array($cssID[0], $varValue);
        }

        return $varValue;
    }

    /**
     * Reset all StyleManager classes from css class field
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     * @param $strTable
     *
     * @return mixed
     */
    private function resetStyleManagerClasses($varValue, $dc, $strTable)
    {
        if($this->isMultipleField($dc->field))
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $objStyles = StyleManagerModel::findByTable($strTable);
        $arrStyles = array();

        if($objStyles !== null)
        {
            while($objStyles->next())
            {
                $arrGroup = \StringUtil::deserialize($objStyles->cssClasses, true);

                foreach ($arrGroup as $opts)
                {
                    $arrStyles[] = ' ' . $opts['key'] . ' ';
                }
            }

            $arrStyles = array_filter($arrStyles);
        }

        if(count($arrStyles))
        {
            $varValue  = ' ' . $varValue . ' ';

            $varValue = str_replace($arrStyles, ' ', $varValue);
            $varValue = trim(preg_replace('#\s+#', ' ', $varValue));
        }

        if($this->isMultipleField($dc->field))
        {
            $varValue = serialize(array($cssID[0], $varValue));
        }

        return $varValue;
    }

    /**
     * Update classes on multi edit
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function updateOnMultiEdit($varValue, $dc)
    {
        if (\Input::get('act') === 'editAll')
        {
            if($field = $this->getClassFieldNameByTable($dc->table))
            {
                $stdClass = $dc;
                $stdClass->field = $field;
                $stdClass->activeRecord->styleManager = $varValue;

                // Get new value
                $value = $this->resetStyleManagerClasses($dc->activeRecord->{$field}, $stdClass, $dc->table);
                $value = $this->updateStyleManager($value, $stdClass);
                $value = $this->isMultipleField($field) ? serialize($value) : $value;

                // Update css class field
                $dc->Database->prepare('UPDATE ' . $dc->table . ' SET ' . $field . '=? WHERE id=?')
                             ->execute($value, $dc->activeRecord->id);
            }
        }

        return $varValue;
    }

    /**
     * Return the field name of css classes by table
     *
     * @param $strTable
     *
     * @return mixed
     */
    public function getClassFieldNameByTable($strTable)
    {
        \Backend::loadDataContainer($strTable);

        foreach ($this->validCssClassFields as $field => $size)
        {
            if(isset($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $field ]))
            {
                return $field;
            }
        }

        return false;
    }

    /**
     * Checks the passed array and removes non-existent values
     *
     * @param $strField
     *
     * @return bool
     */
    public function isMultipleField($strField)
    {
        return $this->validCssClassFields[ $strField ] > 1;
    }

    /**
     * Checks the passed array and removes non-existent values
     *
     * @param $arrValues
     * @param $strTable
     */
    public function cleanupClasses(&$arrValues, $strTable)
    {
        if(is_array($arrValues))
        {
            $objStyles = StyleManagerModel::findByTable($strTable);

            if($objStyles !== null)
            {
                $arrExistingKeys = array();
                $arrExistingValues = array();

                while($objStyles->next())
                {
                    $arrExistingKeys[] = $objStyles->alias;

                    $arrGroup = \StringUtil::deserialize($objStyles->cssClasses, true);

                    foreach ($arrGroup as $opts)
                    {
                        $arrExistingValues[] = $opts['key'];
                    }
                }

                foreach ($arrValues as $key => $value)
                {
                    if(!in_array($key, $arrExistingKeys))
                    {
                        unset($arrValues[$key]);
                        continue;
                    }

                    if(!in_array($value, $arrExistingValues))
                    {
                        unset($arrValues[$key]);
                    }
                }
            }
            else
            {
                $arrValues = array();
            }
        }
    }

    /**
     * Add a new regexp "variable"
     * @param $strRegexp
     * @param $varValue
     * @param \Widget $objWidget
     * @return bool
     */
    public function addVariableRegexp($strRegexp, $varValue, \Widget $objWidget)
    {
        if ($strRegexp == 'variable')
        {
            if (!preg_match('/^[a-zA-Z](?:_?[a-zA-Z0-9]+)$/', $varValue))
            {
                $objWidget->addError('Field ' . $objWidget->label . ' must begin with a letter and may not contain any spaces or special characters (e.g. myVariable).');
            }

            return true;
        }

        return false;
    }
}
