<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

class Styles
{
    /**
     * Style Collection
     * @var array|null
     */
    private $styles = null;

    /**
     * Current identifier
     * @var string
     */
    private $currIdentifier = '';

    /**
     * Current groups
     * @var array|null
     */
    private $currGroups = null;

    /**
     * Initialize the object
     *
     * @param array $arrStyles
     */
    public function __construct($arrStyles=null)
    {
        $this->styles = $arrStyles;
    }

    /**
     * Return the css class collection of an identifier
     *
     * @param $identifier
     * @param null $arrGroups
     *
     * @return string
     */
    public function get($identifier, $arrGroups=null)
    {
        if($this->styles === null || !is_array($this->styles[ $identifier ]))
        {
            return '';
        }

        // return full collection
        if($arrGroups === null)
        {
            return implode(" ", $this->getCategoryValues($this->styles[ $identifier ]));
        }

        // return parts of category (groups)
        if(is_array($arrGroups))
        {
            $collection = array();

            foreach ($arrGroups as $groupAlias)
            {
                if($value = $this->getGroupValue($this->styles[ $identifier ][ $groupAlias ]))
                {
                    $collection[] = $value;
                }
            }

            return  implode(" ", $collection);
        }

        return '';
    }

    /**
     * Prepare css classes
     *
     * @param $identifier
     * @param null $arrGroups
     *
     * @return Styles
     */
    public function prepare($identifier, $arrGroups=null)
    {
        $this->currIdentifier = $identifier;
        $this->currGroups = $arrGroups;

        return $this;
    }

    /**
     * Return formatted css classes
     *
     * @param $format
     * @param string $method
     *
     * @return string
     */
    public function format($format, $method='')
    {
        if(!$format || $this->styles === null || !is_array($this->styles[ $this->currIdentifier ]))
        {
            return '';
        }

        switch($method)
        {
            case 'json':
                $arrValues = null;

                // return full collection
                if($this->currGroups === null)
                {
                    foreach ($this->styles[ $this->currIdentifier ] as $alias => $arrVariable)
                    {
                        if(($value = $this->getGroupValue($arrVariable)) !== '')
                        {
                            $arrValues[ $alias ] = $this->parseValueType($value);
                        }
                    }
                }
                // return parts of category (groups)
                else
                {
                    foreach ($this->currGroups as $alias)
                    {
                        if(($value = $this->getGroupValue($this->styles[ $this->currIdentifier ][ $alias ])) !== '')
                        {
                            $arrValues[ $alias ] = $this->parseValueType($value);
                        }
                    }
                }

                if($arrValues !== null && $jsonValue = json_encode($arrValues))
                {
                    return sprintf($format, $jsonValue);
                }

                break;

            default:
                // HOOK: add custom logic format methods
                if (isset($GLOBALS['TL_HOOKS']['styleManagerFormatMethod']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerFormatMethod']))
                {
                    foreach ($GLOBALS['TL_HOOKS']['styleManagerFormatMethod'] as $callback)
                    {
                        return \System::importStatic($callback[0])->{$callback[1]}($format, $method, $this);
                    }
                }

                if($value = $this->get($this->currIdentifier, $this->currGroups))
                {
                    return sprintf($format, $value);
                }
        }

        return '';
    }

    /**
     * Return all values of a category
     *
     * @param $arrVariables
     *
     * @return array
     */
    private function getCategoryValues($arrVariables)
    {
        $arrValues = array();

        foreach ($arrVariables as $alias => $arrVariable)
        {
            $arrValues[] = $this->getGroupValue($arrVariable);
        }

        return $arrValues;
    }

    /**
     * Return the value of a group
     *
     * @param $arrVariable
     *
     * @return mixed
     */
    private function getGroupValue($arrVariable)
    {
        return $arrVariable['value'];
    }

    /**
     * Return the value as correct type
     *
     * @param $strValue
     *
     * @return mixed
     */
    private function parseValueType($strValue)
    {
        if(is_numeric($strValue))
        {
            return (float) $strValue;
        }
        elseif(strtolower($strValue) === 'true')
        {
            return true;
        }
        elseif(strtolower($strValue) === 'false')
        {
            return false;
        }

        return $strValue;
    }
}
