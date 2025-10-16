<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

use Contao\System;

/**
 * @internal
 */
class Styles
{
    /**
     * Current identifier
     */
    private string $currIdentifier = '';

    /**
     * Current groups
     */
    private array|null $currGroups = null;

    /**
     * Excluded groups
     */
    private array|null $exclGroups = null;

    /**
     * Initialize the object.
     */
    public function __construct(
        private readonly array|null $styles = null // Style collection
    ) {
    }

    /**
     * Return the CSS class collection of an identifier
     */
    public function get(string|int $identifier, $arrGroups = null): string
    {
        if ($this->styles === null || !is_array(($this->styles[ $identifier ] ?? null)))
        {
            return '';
        }

        // return the full collection
        if ($arrGroups === null)
        {
            return implode(" ", $this->getCategoryValues($this->styles[ $identifier ]));
        }

        // return parts of category (groups)
        if (is_array($arrGroups))
        {
            $collection = array();

            if (null !== $this->exclGroups)
            {
                $this->removeExcludedGroups($arrGroups);
            }

            foreach ($arrGroups as $groupAlias)
            {
                if ($value = $this->getGroupValue($this->styles[ $identifier ][ $groupAlias ] ?? null))
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
    public function prepare(string|int $identifier, array|null $arrGroups = null): Styles
    {
        $this->currIdentifier = $identifier;
        $this->currGroups     = $arrGroups;
        $this->exclGroups     = null;

        return $this;
    }

    /**
     * Exclude css classes
     */
    public function exclude(array|null $exclGroups = null): Styles
    {
        $this->exclGroups = $exclGroups;

        return $this;
    }

    /**
     * Return formatted CSS classes
     */
    public function format(string $format, string $method=''): string
    {
        if (!$format || $this->styles === null || !is_array(($this->styles[ $this->currIdentifier ] ?? null)))
        {
            return '';
        }

        switch ($method)
        {
            case 'json':
                $arrValues = null;

                // return the full collection
                if ($this->currGroups === null)
                {
                    foreach ($this->styles[ $this->currIdentifier ] as $alias => $arrVariable)
                    {
                        if (($value = $this->getGroupValue($arrVariable)) !== '')
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
                        if (($value = $this->getGroupValue($this->styles[ $this->currIdentifier ][ $alias ] ?? null)) !== '')
                        {
                            $arrValues[ $alias ] = $this->parseValueType($value);
                        }
                    }
                }

                if (null !== $this->exclGroups)
                {
                    $arrValues = array_diff($arrValues ?? [], $this->exclGroups);
                }

                if ($arrValues !== null && $jsonValue = json_encode($arrValues))
                {
                    return sprintf($format, $jsonValue);
                }

                break;

            default:
                // ToDo: Event or is this even needed? Has not been used. People should decorate this service instead
                if ($value = $this->get($this->currIdentifier, $this->currGroups))
                {
                    return sprintf($format, $value);
                }
        }

        return '';
    }

    /**
     * Return all values of a category
     */
    private function getCategoryValues(mixed $arrVariables): array
    {
        $arrValues = [];

        if (null !== $this->exclGroups)
        {
            $this->removeExcludedGroups($arrVariables);
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
    private function getGroupValue(mixed $arrVariable)
    {
        return $arrVariable['value'] ?? null;
    }

    /**
     * Return the value as correct type
     */
    private function parseValueType(mixed $strValue)
    {
        if (is_numeric($strValue))
        {
            return (float) $strValue;
        }

        if ('true' === strtolower((string) $strValue))
        {
            return true;
        }

        if ('false' === strtolower((string) $strValue))
        {
            return false;
        }

        return $strValue;
    }

    /**
     * Removes excluded groups
     */
    private function removeExcludedGroups(array &$currGroups): void
    {
        if (null === $this->exclGroups)
        {
            return;
        }

        foreach ($this->exclGroups as $exclude)
        {
            if (array_key_exists($exclude, $currGroups))
            {
                unset($currGroups[$exclude]);
            }
            // in case the excluded group is a value -> when a group was passed
            else if (in_array($exclude, $currGroups))
            {
                $currGroups = array_diff($currGroups, [$exclude]);
            }
        }
    }
}
