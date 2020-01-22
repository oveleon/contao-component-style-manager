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
    public static $validCssClassFields = array(
        'cssID'      => 2,
        'cssClass'   => 1,
        'class'      => 1,
        'attributes' => 2
    );

    /**
     * Load callback for the CSS-Classes DCA-Field
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function onLoad($varValue, $dc)
    {
        return self::clearClasses($varValue, $dc);
    }

    /**
     * Save callback for the CSS-Classes DCA-Field
     *
     * @param $varValue
     * @param $dc
     *
     * @return mixed
     */
    public function onSave($varValue, $dc)
    {
        return self::updateClasses($varValue, $dc);
    }

    /**
     * Clear StyleManager classes from css class field
     *
     * @param mixed $varValue
     * @param $dc
     *
     * @return mixed
     */
    public static function clearClasses($varValue, $dc)
    {
        if(self::isMultipleField($dc->field))
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $arrValues = \StringUtil::deserialize($dc->activeRecord->styleManager, true);
        $arrValues = self::deserializeValues($arrValues);

        // remove non-exiting values
        self::cleanupClasses($arrValues, $dc->table);

        if(count($arrValues))
        {
            foreach ($arrValues as $k => $v)
            {
                $arrValues[ $k ] = ' ' . $v . ' ';
            }

            $varValue = ' ' . $varValue . ' ';
            $varValue = str_replace($arrValues, '  ', $varValue);
            $varValue = trim(preg_replace('#\s+#', ' ', $varValue));
        }

        if(self::isMultipleField($dc->field))
        {
            $varValue = serialize(array($cssID[0], $varValue));
        }

        return $varValue;
    }

    /**
     * Update StyleManager classes
     *
     * @param mixed $varValue
     * @param $dc
     *
     * @return mixed
     */
    public static function updateClasses($varValue, $dc)
    {
        if(self::isMultipleField($dc->field))
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $varValues = \StringUtil::deserialize($dc->activeRecord->styleManager, true);

        // remove vars node
        if(isset($varValues['__vars__']))
        {
            unset($varValues['__vars__']);
        }

        // append classes
        $varValue .= ($varValue ? ' ' : '') . (count($varValues) ? implode(' ', $varValues) : '');
        $varValue  = trim($varValue);

        if(self::isMultipleField($dc->field))
        {
            $varValue = array($cssID[0], $varValue);
        }

        return $varValue;
    }

    /**
     * Reset all StyleManager classes from css class field
     *
     * @param mixed $varValue
     * @param $dc
     * @param $strTable
     *
     * @return mixed
     */
    public static function resetClasses($varValue, $dc, $strTable)
    {
        if(self::isMultipleField($dc->field))
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

        if(self::isMultipleField($dc->field))
        {
            $varValue = serialize(array($cssID[0], $varValue));
        }

        return $varValue;
    }

    /**
     * Checks the passed array and removes non-existent values
     *
     * @param $arrValues
     * @param $strTable
     */
    public static function cleanupClasses(&$arrValues, $strTable)
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
                    $arrExistingKeys[] = $objStyles->id;

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
     * Return the field name of css classes by table
     *
     * @param $strTable
     *
     * @return mixed
     */
    public static function getClassFieldNameByTable($strTable)
    {
        \Backend::loadDataContainer($strTable);

        foreach (self::$validCssClassFields as $field => $size)
        {
            if(isset($GLOBALS['TL_DCA'][ $strTable ]['fields'][ $field ]))
            {
                return $field;
            }
        }

        return false;
    }

    /**
     * Checks whether a field is multiple
     *
     * @param $strField
     *
     * @return bool
     */
    public static function isMultipleField($strField)
    {
        return self::$validCssClassFields[ $strField ] > 1;
    }

    /**
     * Moves classes which should be passed to the template to the "vars" node
     *
     * @param $varValue
     * @param $strTable
     *
     * @return array|bool
     */
    public static function serializeValues($varValue, $strTable){
        $objStyleGroups = StyleManagerModel::findByTable($strTable);

        if($objStyleGroups === null)
        {
            return false;
        }

        $arrArchives = array();
        $objStyleArchives = StyleManagerArchiveModel::findAll();

        // Prepare archives identifier
        while($objStyleArchives->next())
        {
            $arrArchives[ $objStyleArchives->id ] =  $objStyleArchives->identifier;
        }

        // Remove unused classes
        $arrValue = array_filter($varValue);

        // Rebuild array for template variables
        while($objStyleGroups->next())
        {
            if(array_key_exists($objStyleGroups->id, $arrValue))
            {
                if(!!$objStyleGroups->passToTemplate)
                {
                    $identifier = $arrArchives[ $objStyleGroups->pid ];

                    $arrValue['__vars__'][ $identifier ][ $objStyleGroups->alias ] = array(
                        'id'    => $objStyleGroups->id,
                        'value' => $arrValue[ $objStyleGroups->id ]
                    );

                    unset($arrValue[ $objStyleGroups->id ]);
                }
            }
        }

        return $arrValue;
    }

    /**
     * Restore the default value of the StyleManager Widget (without vars node)
     *
     * @param $arrValue
     *
     * @return mixed
     */
    public static function deserializeValues($arrValue){

        if(isset($arrValue['__vars__']))
        {
            foreach ($arrValue['__vars__'] as $key => $values)
            {
                foreach ($values as $alias => $arrItem)
                {
                    $arrValue[ $arrItem['id'] ] = $arrItem['value'];
                }
            }

            unset($arrValue['__vars__']);
        }

        return $arrValue;
    }

    /**
     * Parse Template and set Variables
     *
     * @param $template
     */
    public function onParseTemplate($template)
    {
        if(!($template->styleManager instanceof Styles))
        {
            $arrStyles = \StringUtil::deserialize($template->styleManager);
            $template->styleManager = new Styles(isset($arrStyles['__vars__']) ? $arrStyles['__vars__'] : null);
        }
    }

    /**
     * Add a new regexp "variable"
     *
     * @param $strRegexp
     * @param $varValue
     * @param \Widget $objWidget
     *
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
