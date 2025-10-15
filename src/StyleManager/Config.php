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
use Oveleon\ContaoComponentStyleManager\Controller\BackendModule\ImportController;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
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

        if (
            ($xmlFiles = $this->getBundleConfigurationFiles(ConfigurationFileType::XML))
            && (is_array($xmlBundleConfig = ImportController::importXmlFiles($xmlFiles, false)))
        ) {
            $configuration = $xmlBundleConfig;
        }

        if ($yamlFiles = $this->getBundleConfigurationFiles(ConfigurationFileType::YAML))
        {
            $configuration = self::parseYamlConfiguration($yamlFiles, $configuration);
        }

        return $configuration;
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
            $filePath = $rootDir . '/' . $filePath;

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
                    $archive = $styleArchives[$archiveIdent];
                }
                else
                {
                    $archive = new StyleManagerArchiveModel();
                    $archive->identifier = $archiveIdent;
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
                        $child = $styleGroups[$styleIdent];
                    }
                    else
                    {
                        $child = new StyleManagerModel();
                        $child->alias = $childAlias;

                        // Fake the pid for the ComponentStyleSelect Widget
                        $child->pid = $archiveIdent;
                    }

                    // ToDo: Generalize that in future with the part in the ImportController
                    foreach ($childData as $kk => $vv)
                    {
                        if ($kk === 'id' || !$vv || (!is_array($vv) && strtolower((string) $vv) === 'null'))
                        {
                            continue;
                        }

                        switch ($kk)
                        {
                            case 'pid':
                                $vv = $child->pid;
                                break;

                            case 'cssClasses':
                                if (is_array($vv))
                                {
                                    $vv = $this->convertCssClasses($vv);
                                }

                                if ($child->{$kk})
                                {
                                    /** @var array<array<int|string>> $arrClasses */
                                    $arrClasses = StringUtil::deserialize($child->{$kk}, true);
                                    $arrExists  = Sync::flattenKeyValueArray($arrClasses);

                                    /** @var array<array<int|string>> $arrValues */
                                    $arrValues  = StringUtil::deserialize($vv, true);

                                    foreach ($arrValues as $cssClass)
                                    {
                                        if (!array_key_exists($cssClass['key'], $arrExists))
                                        {
                                            $arrClasses[] = [
                                                'key'   => $cssClass['key'],
                                                'value' => $cssClass['value']
                                            ];
                                        }
                                    }

                                    $vv = serialize($arrClasses);
                                }
                                break;

                            default:
                                $dcaField = $GLOBALS['TL_DCA']['tl_style_manager']['fields'][$kk] ?? null;

                                if (isset($dcaField['eval']['multiple']) && !!$dcaField['eval']['multiple'] && $dcaField['inputType'] === 'checkbox')
                                {
                                    /** @var array<array<int|string>> $arrElements */
                                    $arrElements = StringUtil::deserialize($child->{$kk}, true);

                                    /** @var array<array<int|string>> $arrValues */
                                    $arrValues = StringUtil::deserialize($vv, true);

                                    foreach ($arrValues as $element)
                                    {
                                        if (!in_array($element, $arrElements, true))
                                        {
                                            $arrElements[] = $element;
                                        }
                                    }

                                    $vv = serialize($arrElements);
                                }
                        }

                        $child->{$kk} = $vv;
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
}
