<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

use Contao\Backend;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\File;
use Contao\Message;
use Contao\Model\Collection;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\Column;
use DOMDocument;
use DOMElement;
use DOMNode;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Psr\Log\LoggerInterface;

/**
 * @internal
 *
 * @deprecated since Version 3.11, to be removed in Version 4.
 */
class Sync
{
    public function __construct(
        protected ContaoFramework $framework,
        protected Connection $connection,
        private readonly LoggerInterface|null $logger = null
    ) {
    }

    /**
     * Check if the object conversion should be performed
     *
     * @throws Exception
     */
    public function shouldRunObjectConversion($table = null): bool
    {
        $this->framework->initialize();

        $schemaManager = $this->connection->createSchemaManager();

        if (null === $table || !$schemaManager->tablesExist([$table]))
        {
            return false;
        }

        $columnNames = array_map(static function (Column $column): string {
            return $column->getName();
        }, $schemaManager->listTableColumns($table));

        if (!\in_array('styleManager', $columnNames, true)) {
            return false;
        }

        $objConfig = $this->connection->fetchFirstColumn("SELECT styleManager FROM " . $table . " WHERE styleManager IS NOT NULL");
        $archives = StyleManagerArchiveModel::countAll();

        if (count($objConfig) && $archives > 0 && $arrConfig = StringUtil::deserialize($objConfig[0]))
        {
            $key = array_key_first($arrConfig);

            if ($key === StyleManager::VARS_KEY && count($arrConfig) > 1)
            {
                $key = array_keys($arrConfig)[1];
            }

            if (is_numeric($key) && empty(StyleManagerModel::findById($key)))
            {
                // Skip if the configuration was already converted or cannot be found anymore as the PK does not exist
                $this->logger->error('Style Manager conversion for table "'.$table.'" and id "'.$key.'" has been skipped due to primary key not existing anymore.');
                return false;
            }

            return is_numeric($key);
        }

        return false;
    }

    /**
     * Perform the object conversion
     *
     * @throws Exception
     */
    public function performObjectConversion($table = null): void
    {
        $this->framework->initialize();

        $schemaManager = $this->connection->createSchemaManager();

        if (null === $table || !$schemaManager->tablesExist([$table]))
        {
            return;
        }

        $objRows = $this->connection->fetchAllAssociative("SELECT id, styleManager FROM " . $table . " WHERE styleManager IS NOT NULL");

        if (!empty($objRows))
        {
            $objArchives = StyleManagerArchiveModel::findAll();
            $arrArchives = [];

            if (null === $objArchives)
            {
                return;
            }

            foreach ($objArchives as $objArchive)
            {
                $arrArchives[ $objArchive->id ] = $objArchive->identifier;
            }

            $arrIds = [];

            foreach ($objRows as $rows)
            {
                if ($arrConfig = StringUtil::deserialize($rows['styleManager']))
                {
                    $arrIds = array_merge($arrIds, array_keys($arrConfig));
                }
            }

            $objGroups = StyleManagerModel::findMultipleByIds(array_unique($arrIds));
            $arrGroups = [];

            if (null !== $objGroups)
            {
                foreach ($objGroups as $objGroup)
                {
                    $arrGroups[ $objGroup->id ] = $objGroup;
                }

                foreach ($objRows as $arrRow)
                {
                    $config = StringUtil::deserialize($arrRow['styleManager']);

                    $key = array_key_first($config);

                    if ($key === StyleManager::VARS_KEY && count($config) > 1)
                    {
                        $key = array_keys($config)[1];
                    }

                    // Skip if the configuration was already converted
                    if (!is_numeric($key))
                    {
                        continue;
                    }

                    $arrAliasPairKeys = array_map(function($intGroupKey) use ($arrArchives, $arrGroups) {
                        return $intGroupKey === StyleManager::VARS_KEY ? StyleManager::VARS_KEY : StyleManager::generateAlias($arrArchives[ $arrGroups[$intGroupKey]->pid ], $arrGroups[$intGroupKey]->alias);
                    }, array_keys($config));

                    $newConfig = array_combine($arrAliasPairKeys, $config);

                    $this->connection->executeStatement("
                        UPDATE
                            ".$table."
                        SET
                            styleManager = ?
                        WHERE
                            id = ?
                    ", [serialize($newConfig), $arrRow['id']]);
                }
            }
        }
    }

    /**
     * Merge group objects
     */
    public static function mergeGroupObjects(StyleManagerModel|null $objOriginal = null, StyleManagerModel|null $objMerge = null, array|null $skipFields = null, bool $skipEmpty = true, $forceOverwrite = true): StyleManagerModel|null
    {
        if (null === $objOriginal || null === $objMerge)
        {
            return $objOriginal;
        }

        Controller::loadDataContainer('tl_style_manager');

        foreach ($objMerge->row() as $field => $value)
        {
            if (
                ($skipEmpty && (!$value || strtolower((string) $value) === 'null'))
                || (null !== $skipFields && in_array($field, $skipFields))
            ) {
                continue;
            }

            switch ($field)
            {
                // Merge and manipulation of existing classes
                case 'cssClasses':
                    if ($objOriginal->{$field})
                    {
                        /** @var array $arrClasses */
                        $arrClasses = StringUtil::deserialize($objOriginal->{$field}, true);
                        $arrExists = self::flattenKeyValueArray($arrClasses);

                        /** @var array $arrValues */
                        $arrValues = StringUtil::deserialize($value, true);

                        foreach ($arrValues as $cssClass)
                        {
                            if (array_key_exists($cssClass['key'], $arrExists))
                            {
                                if (!$forceOverwrite)
                                {
                                    continue;
                                }

                                // Overwrite existing value
                                if (false !== ($key = array_search($cssClass['key'], array_column($arrClasses, 'key'))))
                                {
                                    $arrClasses[ $key ] = [
                                        'key' => $cssClass['key'],
                                        'value' => $cssClass['value']
                                    ];
                                }

                                continue;
                            }

                            $arrClasses[] = [
                                'key' => $cssClass['key'],
                                'value' => $cssClass['value']
                            ];
                        }

                        $value  = serialize($arrClasses);
                    }

                    break;
                // Check for multiple fields like contentElement
                default:
                    $fieldOptions = $GLOBALS['TL_DCA']['tl_style_manager']['fields'][$field];

                    if (isset($fieldOptions['eval']['multiple']) && !!$fieldOptions['eval']['multiple'] && $fieldOptions['inputType'] === 'checkbox')
                    {
                        /** @var array $arrElements */
                        $arrElements = StringUtil::deserialize($objOriginal->{$field}, true);

                        /** @var array $arrValues */
                        $arrValues = StringUtil::deserialize($value, true);

                        foreach ($arrValues as $element)
                        {
                            if (in_array($element, $arrElements))
                            {
                                if (!$forceOverwrite)
                                {
                                    continue;
                                }

                                $key = array_search($element, $arrElements);
                                $arrElements[ $key ] = $element;

                                continue;
                            }

                            $arrElements[] = $element;
                        }

                        $value  = serialize($arrElements);
                    }
            }

            // Overwrite field values
            $objOriginal->{$field} = $value;
        }

        return $objOriginal;
    }

    /**
     * Export StyleManager records
     * @throws \Exception
     */
    public function export(DataContainer|null $dc = null, $objArchives = null, bool $blnSendToBrowser = true)
    {
        $this->framework->initialize();

        // Create a new XML document
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Archives
        if (null === $objArchives)
        {
            $objArchives = StyleManagerArchiveModel::findAll(['order' => 'groupAlias,sorting']);
        }

        if (null === $objArchives)
        {
            Message::addError($GLOBALS['TL_LANG']['ERR']['noStyleManagerConfigFound']);
            Backend::redirect(Backend::getReferer());
        }

        // Root element
        $archives = $xml->createElement('archives');
        $archives = $xml->appendChild($archives);

        // Add the archives
        while ($objArchives->next())
        {
            $this->addArchiveData($xml, $archives, $objArchives);
        }

        // Generate temp name
        $strTmp = md5(uniqid((string) mt_rand(), true));

        // Create a file and open the "save as …" dialogue
        $objFile = new File('system/tmp/' . $strTmp);
        $objFile->write($xml->saveXML());
        $objFile->close();

        if (!$blnSendToBrowser)
        {
            return $objFile;
        }

        $objFile->sendToBrowser('style-manager-export.xml');
    }

    /**
     * Add an archive data row to the XML document
     * @throws \DOMException
     */
    protected function addArchiveData(DOMDocument $xml, DOMNode $archives, Collection $objArchive): void
    {
        Controller::loadDataContainer('tl_style_manager_archive');

        // Add archive node
        $row = $xml->createElement('archive');
        $row->setAttribute('identifier', $objArchive->identifier);
        $row = $archives->appendChild($row);

        // Add field data
        $this->addRowData($xml, $row, $objArchive->row());

        // Add children data
        $this->addChildrenData($xml, $row, $objArchive->id);
    }

    /**
     * Add a children data row to the XML document
     *
     * @throws \DOMException
     */
    protected function addChildrenData(DOMDocument $xml, DOMElement $archive, int $pid): void
    {
        // Add children node
        $children = $xml->createElement('children');
        $children = $archive->appendChild($children);

        $objChildren = StyleManagerModel::findByPid($pid);

        if ($objChildren === null)
        {
            return;
        }

        Controller::loadDataContainer('tl_style_manager');

        while ($objChildren->next())
        {
            $row = $xml->createElement('child');
            $row->setAttribute('alias', $objChildren->alias);
            $row = $children->appendChild($row);

            // Add field data
            $this->addRowData($xml, $row, $objChildren->row());
        }
    }

    /**
     * Add field data to the XML document
     *
     * @throws \DOMException
     */
    protected function addRowData(DOMDocument $xml, DOMNode $row, array $arrData): void
    {
        foreach ($arrData as $k => $v)
        {
            $field = $xml->createElement('field');
            $field->setAttribute('title', $k);
            $field = $row->appendChild($field);

            if ($v === null)
            {
                $v = 'NULL';
            }

            $value = $xml->createTextNode((string) $v);
            $field->appendChild($value);
        }
    }

    /**
     * Flatten Key Value Array
     */
    public static function flattenKeyValueArray(array $array): array
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

    public static function combineAliases(...$aliases): string
    {
        return implode('_', $aliases);
    }
}
