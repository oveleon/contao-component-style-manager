<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

use Contao\System;

class Styles
{
    /**
     * Style Collection
     */
    private ?array $styles;

    /**
     * Current identifier
     */
    private string $currIdentifier = '';

    /**
     * Current groups
     */
    private ?array $currGroups = null;

    /**
     * Excluded groups
     */
    private ?array $excludedGroups = null;

    /**
     * Initialize the object
     */
    public function __construct(?array $arrStyles = null)
    {
        $this->styles = $arrStyles;
    }

    /**
     * Return the css class collection of an identifier
     */
    public function get($identifier, $arrGroups=null, $excludedGroups=null): string
    {
        if($this->styles === null || !is_array(($this->styles[ $identifier ] ?? null)))
        {
            return '';
        }

        $this->excludedGroups = $excludedGroups;

        // return full collection
        if($arrGroups === null)
        {
            return implode(" ", $this->getCategoryValues($this->styles[ $identifier ]));
        }

        // return parts of category (groups)
        if(is_array($arrGroups))
        {
            $collection = array();

            if(null !== $this->excludedGroups)
            {
                $this->removeExcludedGroups($arrGroups, $this->excludedGroups);
            }

            foreach ($arrGroups as $groupAlias)
            {
                if($value = $this->getGroupValue($this->styles[ $identifier ][ $groupAlias ] ?? null))
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
     */
    public function prepare($identifier, $arrGroups=null, $excludedGroups=null): Styles
    {
        $this->currIdentifier = $identifier;
        $this->currGroups     = $arrGroups;
        $this->excludedGroups = $excludedGroups;

        return $this;
    }

    /**
     * Return formatted css classes
     */
    public function format(string $format, string $method=''): string
    {
        if(!$format || $this->styles === null || !is_array(($this->styles[ $this->currIdentifier ] ?? null)))
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
                        if(($value = $this->getGroupValue($this->styles[ $this->currIdentifier ][ $alias ] ?? null)) !== '')
                        {
                            $arrValues[ $alias ] = $this->parseValueType($value);
                        }
                    }
                }

                if(null !== $this->excludedGroups)
                {
                    $arrValues = array_diff($arrValues, $this->excludedGroups);
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
                        return System::importStatic($callback[0])->{$callback[1]}($format, $method, $this);
                    }
                }

                if($value = $this->get($this->currIdentifier, $this->currGroups, $this->excludedGroups))
                {
                    return sprintf($format, $value);
                }
        }

        return '';
    }

    /**
     * Return all values of a category
     */
    private function getCategoryValues($arrVariables): array
    {
        $arrValues = [];

        if (null !== $this->excludedGroups)
        {
            $this->removeExcludedGroups($arrVariables, $this->excludedGroups);
        }

        foreach ($arrVariables as $alias => $arrVariable)
        {
            $arrValues[] = $this->getGroupValue($arrVariable);
        }

        return $arrValues;
    }

    /**
     * Return the value of a group
     */
    private function getGroupValue($arrVariable)
    {
        return $arrVariable['value'] ?? null;
    }

    /**
     * Return the value as correct type
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

    /**
     * Removes excluded groups
     */
    private function removeExcludedGroups(array &$groups, array $excludedGroups): void
    {
        foreach ($excludedGroups as $exclude)
        {
            if (array_key_exists($exclude, $groups))
            {
                unset($groups[$exclude]);
            }
            // in case the excluded group is a value -> when a group was passed
            else if (in_array($exclude, $groups))
            {
                $groups = array_diff($groups, [$exclude]);
            }
        }
    }
}
