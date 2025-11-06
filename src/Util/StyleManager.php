<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Util;

use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\Style\StyleGroup;

/**
 * @internal
 */
class StyleManager
{
    public const string VARS_KEY = '__vars__';

    /**
     * Valid CSS-Class fields [field => size]
     */
    public static array $validCssClassFields = [
        'cssID'      => 2,
        'cssClass'   => 1,
        'class'      => 1,
        'attributes' => 2
    ];

    /**
     * Restore the default value of the StyleManager Widget (without the __vars__ node)
     */
    public static function deserializeValues(mixed $arrValue): mixed
    {
        if (!isset($arrValue[self::VARS_KEY]))
        {
            return $arrValue;
        }

        foreach ($arrValue[StyleManager::VARS_KEY] as $archiveAlias => $values)
        {
            foreach ($values as $alias => $arrItem)
            {
                $strId = self::generateAlias($archiveAlias, $alias);
                $arrValue[$strId] = html_entity_decode((string) $arrItem['value']);
            }
        }

        unset($arrValue[StyleManager::VARS_KEY]);

        return $arrValue;
    }

    /**
     * Generate a unique alias based on the archive identifier and the group alias
     */
    public static function generateAlias(mixed $identifier, mixed $alias): string
    {
        return $identifier . '_' . $alias;
    }

    /**
     * Moves classes which should be passed to the template to the "vars" node
     */
    public static function serializeValues(mixed $varValue, string $strTable): bool|array
    {
        $objStyleGroups = StyleManagerModel::findByTableAndConfiguration($strTable);

        if ($objStyleGroups === null)
        {
            return false;
        }

        $arrArchives = [];
        $objStyleArchives = StyleManagerArchiveModel::findAllWithConfiguration();

        // Prepare archives identifier
        foreach ($objStyleArchives as $objStyleArchive)
        {
            $arrArchives[$objStyleArchive->id ?? $objStyleArchive->identifier] = $objStyleArchive->identifier;
        }

        // Remove unused classes
        $arrValue = array_filter($varValue, function ($v) {
            return $v !== false && !is_null($v) && ($v != '' || $v == '0');
        });

        // Rebuild the array for template variables
        foreach ($objStyleGroups as $objStyleGroup)
        {
            if (false === self::styleGroupMappableToArchives($objStyleGroup, $arrArchives))
            {
                continue;
            }

            $strId = self::generateAlias($arrArchives[$objStyleGroup->pid], $objStyleGroup->alias);

            if (array_key_exists($strId, $arrValue))
            {
                if (!!$objStyleGroup->passToTemplate)
                {
                    $identifier = $arrArchives[$objStyleGroup->pid];

                    $arrValue[StyleManager::VARS_KEY][$identifier][$objStyleGroup->alias] = [
                        'id' => $objStyleGroup->id,
                        'value' => $arrValue[$strId]
                    ];

                    unset($arrValue[$strId]);
                }
            }
        }

        return $arrValue;
    }

    /**
     * Checks whether a style group is mappable to any existing archives
     */
    public static function styleGroupMappableToArchives(StyleGroup|StyleManagerModel $styleGroup, array $archives): bool
    {
        if (isset($archives[$styleGroup->pid])) {
            return true;
        }

        $pid = $styleGroup instanceof StyleManagerModel ? $styleGroup->originalRow()['pid'] : $styleGroup->pid;

        if (false === ($key = array_search($pid ?? null, $archives, true))) {
            return false;
        }

        // Allow configurations without database identifiers to be merged into existing groups if they exist
        $styleGroup->pid = $key;

        return true;
    }
}
