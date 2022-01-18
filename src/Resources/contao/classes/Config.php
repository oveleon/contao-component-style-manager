<?php

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

use Contao\System;

/**
 * A static class to store config data
 *
 * @author Daniele Sciannimanica <https://github.com/doishub>
 */
class Config
{
    /**
     * Object instance (Singleton)
     * @var Config
     */
    protected static $objInstance;

    /**
     * Group data
     * @var array
     */
    protected static $arrGroups = [];

    /**
     * Archive data
     * @var array
     */
    protected static $arrArchive = [];

    /**
     * Return all archives as array
     */
    public static function getArchives(): ?array
    {
        return static::$arrArchive;
    }

    /**
     * Return all Groups as array
     */
    public static function getGroups(?string $table=null): ?array
    {
        if(null === $table)
        {
            return static::$arrGroups;
        }

        $arrObjStyleGroups = null;

        foreach (static::$arrGroups as $objStyleGroup)
        {
            // Skip if the group is not allowed for the current table
            if(StyleManager::isVisibleGroup($objStyleGroup, $table))
            {
                $arrObjStyleGroups[] = $objStyleGroup;
            }
        }

        return $arrObjStyleGroups;
    }

    /**
     * Load configuration files from third-party bundles and return them as array
     */
    protected function loadBundleConfiguration(): ?array
    {
        if($arrFiles = $this->getBundleConfigurationFiles())
        {
            $sync = new Sync();
            return $sync->importStyleManagerFile($arrFiles, false);
        }

        return null;
    }

    /**
     * Return all configuration files from third-party bundles
     */
    protected function getBundleConfigurationFiles(): ?array
    {
        $arrFiles = System::getContainer()->get('contao.resource_finder')->findIn('templates')->files()->name('style-manager-*.xml');

        if($arrFiles->hasResults())
        {
            $projectDir = System::getContainer()->getParameter('kernel.project_dir');
            $arrBundleConfigs = null;

            foreach ($arrFiles as $file)
            {
                $strRelpath = $file->getRealPath();
                $arrBundleConfigs[basename($strRelpath)] = str_replace($projectDir, '', $strRelpath);
            }

            return $arrBundleConfigs;
        }

        return null;
    }

    /**
     * Prevent direct instantiation (Singleton)
     */
    protected function __construct()
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
     *
     * @return Config The object instance
     */
    public static function getInstance()
    {
        if (static::$objInstance === null)
        {
            static::$objInstance = new static();
        }

        return static::$objInstance;
    }
}