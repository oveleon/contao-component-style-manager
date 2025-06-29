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

/**
 * A static class to store config data
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
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
    public static function getBundleConfigurationFiles(): array|null
    {
        /** @var string $projectDir */
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        $arrFiles = System::getContainer()->get('contao.resource_finder')?->findIn('templates')?->files()?->name('style-manager-*.xml');
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

        if ($projectTemplates = array_merge((glob($projectDir . '/templates/style-manager-*.xml') ?: []), (glob($projectDir . '/templates/*/style-manager-*.xml') ?: [])))
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
        if (
            ($arrFiles = $this->getBundleConfigurationFiles())
            && (is_array($bundleConfig = ImportController::importFiles($arrFiles, false)))
        ) {
            return $bundleConfig;
        }

        return [[],[]];
    }
}
