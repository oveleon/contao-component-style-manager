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
     * Clear StyleManager classes from cssClass field
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     */
    public function clearStyleManager($varValue, $dc)
    {
        if($dc->field === 'cssID')
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $arrValues = \StringUtil::deserialize($dc->activeRecord->styleManager, true);

        if(count($arrValues))
        {
            $varValue = str_replace($arrValues, '', $varValue);
            $varValue = trim(preg_replace('#\s+#', ' ', $varValue));
        }

        if($dc->field === 'cssID')
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
        if($dc->field === 'cssID')
        {
            $cssID = \StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1];
        }

        $varValues = \StringUtil::deserialize($dc->activeRecord->styleManager, true);
        $varValues = array_filter($varValues);
        $varValue .= ($varValue ? ' ' : '') . (count($varValues) ? implode(' ', $varValues) : '');

        if($dc->field === 'cssID')
        {
            $varValue = array($cssID[0], $varValue);
        }

        return $varValue;
    }
}