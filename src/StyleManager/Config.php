<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManager\Entity\StyleGroup;
use Oveleon\ContaoComponentStyleManager\StyleManager\Entity\StyleArchive;
use Symfony\Component\Yaml\Yaml;

/**
 * A static class to store config data
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 *
 * @internal
 */
final class Config
{
    /**
     * Object instance (Singleton)
     */
    protected static Config|null $objInstance = null;

    /**
     * Group data
     */
    protected static array $arrGroups = [];

    /**
     * Archive data
     */
    protected static array $arrArchive = [];

    /**
     * Prevent direct instantiation (Singleton)
     */
    public function __construct()
    {
        [$arrStyleArchives, $arrStyleGroups] = static::loadBundleConfiguration();

        self::$arrArchive = $arrStyleArchives;
        self::$arrGroups = $arrStyleGroups;
    }

    /**
     * Prevent cloning of the object (Singleton)
     */
    final public function __clone()
    {
    }

    /**
     * Instantiate the config object
     */
    public static function getInstance(): Config|null
    {
        if (static::$objInstance === null)
        {
            static::$objInstance = new static();
        }

        return static::$objInstance;
    }

    /**
     * Return all archives as an array
     */
    public static function getArchives(): array|null
    {
        return static::$arrArchive;
    }

    /**
     * Return all groups as an array
     */
    public static function getGroups(string|null $table = null): array|null
    {
        if (null === $table)
        {
            return static::$arrGroups;
        }

        $arrObjStyleGroups = null;

        if (static::$arrGroups)
        {
            foreach (static::$arrGroups as $combinedAlias => $objStyleGroup)
            {
                // Skip if the group is not allowed for the current table
                if (StyleManager::isVisibleGroup($objStyleGroup, $table))
                {
                    $arrObjStyleGroups[ $combinedAlias ] = $objStyleGroup;
                }
            }
        }

        return $arrObjStyleGroups;
    }

    /**
     * Return all configuration files from third-party bundles
     */
    public static function getBundleConfigurationFiles(ConfigurationFileType $type): array|null
    {
        $container = System::getContainer();

        /** @var string $projectDir */
        $projectDir = $container->getParameter('kernel.project_dir');
        $arrFiles = $container->get('contao.resource_finder')?->findIn('templates')?->files()?->name('style-manager-*'. $type->value);
        $arrBundleConfigs = null;

        if ($arrFiles->hasResults())
        {
            foreach ($arrFiles as $file)
            {
                $strRelPath = $file->getRealPath();

                $filePath = str_replace('\\', "/", $strRelPath);
                $vendorPosition = strpos($filePath, '/vendor/');

                if (false !== $vendorPosition)
                {
                    $bundleName = str_replace("/vendor/", "", substr($filePath, $vendorPosition));
                    $srcPosition = strpos($bundleName, '/src');

                    if (false !== $srcPosition)
                    {
                        $bundleName = substr($bundleName, 0, $srcPosition);
                    }
                }
                else
                {
                    $bundleName = 'vendor';
                }

                $arrBundleConfigs[basename($strRelPath) . ' <b>(' . $bundleName . ')</b>'] = str_replace($projectDir, '', $strRelPath);
            }
        }

        if ($projectTemplates = array_merge((glob($projectDir . '/templates/style-manager-*' . $type->value) ?: []), (glob($projectDir . '/templates/*/style-manager-*' . $type->value) ?: [])))
        {
            foreach ($projectTemplates as $template)
            {
                $arrBundleConfigs[basename($template) . ' <b>(/'. dirname(StringUtil::striprootdir($template)) .')</b>'] = str_replace($projectDir, '', $template);
            }
        }

        if ($arrBundleConfigs)
        {
            return $arrBundleConfigs;
        }

        return null;
    }

    /**
     * Load configuration files from third-party bundles and return them as an array
     */
    protected function loadBundleConfiguration(): array
    {
        $configuration = [[],[]];

        if ($xmlFiles = $this->getBundleConfigurationFiles(ConfigurationFileType::XML))
        {
            $configuration = self::parseXmlConfiguration($xmlFiles, $configuration);
        }

        if ($yamlFiles = $this->getBundleConfigurationFiles(ConfigurationFileType::YAML))
        {
            $configuration = self::parseYamlConfiguration($yamlFiles, $configuration);
        }

        return $configuration;
    }

    /**
     * Import config files
     */
    public function parseXmlConfiguration(array $files, array $configuration): array|null
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        [$styleArchives, $styleGroups] = $configuration;

        foreach ($files as $filePath)
        {
            if (!is_file($filePath))
            {
                $filePath = $rootDir . '/' . $filePath;
            }

            if (
                !is_file($filePath)
                || !is_readable($filePath)
                || false === ($content = file_get_contents($filePath))
            ) {
                continue;
            }

            $xml = new \DOMDocument();
            $xml->preserveWhiteSpace = false;

            if (!$xml->loadXML($content))
            {
                continue;
            }

            // Get archives node
            $archives = $xml->getElementsByTagName('archives');

            if (0 === $archives->count())
            {
                return null;
            }
            else
            {
                // Skip archives node
                $archives = $archives->item(0)->childNodes;
            }

            // Check if the archive exists
            $archiveExists = function (string $identifier) use ($styleArchives) : bool
            {
                return array_key_exists($identifier, $styleArchives);
            };

            // Check if children exist
            $childrenExists = function (string $alias) use($styleGroups) : bool
            {
                return array_key_exists($alias, $styleGroups);
            };

            // Loop through the archives
            for ($i = 0; $i < $archives->length; $i++)
            {
                $archive = $archives->item($i)->childNodes;
                $archiveIdent = $archives->item($i)->getAttribute('identifier');

                if ($archiveExists($archiveIdent))
                {
                    /** @var StyleArchive $objArchive */
                    $objArchive = $styleArchives[$archiveIdent];
                }
                else
                {
                    $objArchive = new StyleArchive($archiveIdent);
                }

                // Loop through the archive fields
                for ($a=0; $a<$archive->length; $a++)
                {
                    $strField = $archive->item($a)->nodeName;

                    if ($strField === 'field')
                    {
                        $strName  = $archive->item($a)->getAttribute('title');
                        $strValue = $archive->item($a)->nodeValue ?? '';

                        if (in_array($strName, ['id', 'identifier'], true) || strtolower($strValue) === 'null')
                        {
                            continue;
                        }

                        $objArchive->{$strName} = $strValue;
                    }
                    elseif ($strField === 'children')
                    {
                        $children = $archive->item($a)->childNodes;

                        // Loop through the archives fields
                        for ($c=0; $c<$children->length; $c++)
                        {
                            $alias = $children->item($c)->getAttribute('alias');
                            $fields = $children->item($c)->childNodes;

                            $strChildAlias = StyleManager::generateAlias($objArchive->identifier, $alias);

                            if ($childrenExists($strChildAlias))
                            {
                                /** @var StyleGroup $objChild */
                                $objChild = $styleGroups[$strChildAlias];
                            }
                            else
                            {
                                $objChild = new StyleGroup($alias, $archiveIdent);
                            }

                            // Loop through the children fields
                            for ($f=0; $f<$fields->length; $f++)
                            {
                                $strName = $fields->item($f)->getAttribute('title');
                                $strValue = $fields->item($f)->nodeValue;

                                if (in_array($strName, ['id', 'alias'], true) || !$strValue || strtolower($strValue) === 'null')
                                {
                                    continue;
                                }

                                switch ($strName)
                                {
                                    case 'pid':
                                        $strValue = $objArchive->id ?? $objArchive->identifier;
                                        break;
                                    case 'cssClasses':
                                        if ($objChild->{$strName})
                                        {
                                            /** @var array<array<int|string>> $arrClasses */
                                            $arrClasses = StringUtil::deserialize($objChild->{$strName}, true);
                                            $arrExists  = self::flattenKeyValueArray($arrClasses);

                                            /** @var array<array<int|string>> $arrValues */
                                            $arrValues = StringUtil::deserialize($strValue, true);

                                            foreach ($arrValues as $cssClass)
                                            {
                                                if (array_key_exists($cssClass['key'], $arrExists))
                                                {
                                                    continue;
                                                }

                                                $arrClasses[] = [
                                                    'key' => $cssClass['key'],
                                                    'value' => $cssClass['value']
                                                ];
                                            }

                                            $strValue  = serialize($arrClasses);
                                        }

                                        break;
                                    default:
                                        $dcaField = $GLOBALS['TL_DCA']['tl_style_manager']['fields'][$strName];

                                        if (isset($dcaField['eval']['multiple']) && !!$dcaField['eval']['multiple'] && $dcaField['inputType'] === 'checkbox')
                                        {
                                            /** @var array<array<int|string>> $arrElements */
                                            $arrElements = StringUtil::deserialize($objChild->{$strName}, true);
                                            /** @var array<array<int|string>> $arrValues */
                                            $arrValues   = StringUtil::deserialize($strValue, true);

                                            foreach ($arrValues as $element)
                                            {
                                                if (in_array($element, $arrElements))
                                                {
                                                    continue;
                                                }

                                                $arrElements[] = $element;
                                            }

                                            $strValue  = serialize($arrElements);
                                        }
                                }

                                $objChild->{$strName} = $strValue;
                            }

                            $strKey = StyleManager::generateAlias($objArchive->identifier, $objChild->alias);

                            $styleGroups[ $strKey ] = $objChild;
                        }
                    }
                }

                $styleArchives[ $objArchive->identifier ] = $objArchive;
            }
        }

        return [$styleArchives, $styleGroups];
    }

    /**
     * @experimental
     */
    private function parseYamlConfiguration(array $files, array $configuration): array
    {
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        [$styleArchives, $styleGroups] = $configuration;

        foreach ($files as $filePath)
        {
            if (!is_file($filePath))
            {
                $filePath = $rootDir . '/' . $filePath;
            }

            if (
                !is_file($filePath)
                || !is_readable($filePath)
                || null === ($archives = Yaml::parseFile($filePath))
            ) {
                continue;
            }

            foreach ($archives as $archiveIdent => $data)
            {
                if (isset($styleArchives[$archiveIdent]))
                {
                    /** @var StyleArchive $archive */
                    $archive = $styleArchives[$archiveIdent];
                }
                else
                {
                    $archive = new StyleArchive($archiveIdent);
                }

                foreach ($data as $k => $v)
                {
                    if ($k === 'children')
                    {
                        continue;
                    }

                    $archive->{$k} = $v;
                }

                $styleArchives[$archiveIdent] = $archive;

                if (!isset($data['children']))
                {
                    continue;
                }

                foreach ($data['children'] as $childAlias => $childData)
                {
                    $styleIdent = StyleManager::generateAlias($archiveIdent, $childAlias);

                    if (isset($styleGroups[$styleIdent]))
                    {
                        /** @var StyleGroup $child */
                        $child = $styleGroups[$styleIdent];
                    }
                    else
                    {
                        $child = new StyleGroup($childAlias, $archiveIdent);
                    }

                    foreach ($childData as $kk => $vv)
                    {
                        if ($kk === 'id' || !$vv || (!is_array($vv) && strtolower((string) $vv) === 'null'))
                        {
                            continue;
                        }

                        $child->{$kk} = self::convertChildFieldValue($child, $kk, $vv);
                    }

                    $styleGroups[$styleIdent] = $child;
                }
            }
        }

        return [$styleArchives, $styleGroups];
    }

    private function convertCssClasses(array $array): array
    {
        $return = [];

        foreach ($array as $class => $label)
        {
            $return[] = ['key' => $class, 'value' => $label];
        }

        return $return;
    }

    private function flattenKeyValueArray(array $array): array
    {
        if (empty($array))
        {
            return [];
        }

        $arrTemp = [];

        foreach ($array as $item)
        {
            $arrTemp[ $item['key'] ] = $item['value'];
        }

        return $arrTemp;
    }

    private function convertChildFieldValue(StyleGroup|StyleManagerModel $child, string $key, mixed $value): mixed
    {
        switch ($key)
        {
            case 'pid':
                $value = $child->pid;
                break;

            case 'cssClasses':
                if (is_array($value))
                {
                    $value = $this->convertCssClasses($value);
                }

                if ($child->{$key})
                {
                    /** @var array<array<int|string>> $classList */
                    $classList = StringUtil::deserialize($child->{$key}, true);
                    $existing = self::flattenKeyValueArray($classList);

                    /** @var array<array<int|string>> $arrValues */
                    $arrValues  = StringUtil::deserialize($value, true);

                    foreach ($arrValues as $class)
                    {
                        if (!array_key_exists($class['key'], $existing))
                        {
                            $classList[] = [
                                'key'   => $class['key'],
                                'value' => $class['value']
                            ];
                        }
                    }

                    $value = serialize($classList);
                }
                break;

            default:
                $dcaField = $GLOBALS['TL_DCA']['tl_style_manager']['fields'][$key] ?? null;

                if (isset($dcaField['eval']['multiple']) && !!$dcaField['eval']['multiple'] && $dcaField['inputType'] === 'checkbox')
                {
                    /** @var array<array<int|string>> $elements */
                    $elements = StringUtil::deserialize($child->{$key}, true);

                    /** @var array<array<int|string>> $values */
                    $values = StringUtil::deserialize($key, true);

                    foreach ($values as $element)
                    {
                        if (!in_array($element, $elements, true))
                        {
                            $elements[] = $element;
                        }
                    }

                    $value = serialize($elements);
                }
        }

        return $value;
    }
}
