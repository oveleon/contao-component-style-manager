<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

use Contao\Backend;
use Contao\Controller;
use Contao\DataContainer;
use Contao\File;
use Contao\Message;
use Contao\Model\Collection;
use Contao\StringUtil;
use DOMDocument;
use DOMElement;
use DOMNode;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;

class Sync extends Backend
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if the object conversion should be performed
     */
    public function shouldRunObjectConversion($table = null): bool
    {
        $arrTables = $this->Database->listTables();

        if(null === $table || !in_array($table, $arrTables))
        {
            return false;
        }

        $objConfig = $this->Database->query("SELECT styleManager FROM " . $table . " WHERE styleManager IS NOT NULL LIMIT 0,1");
        $archives = StyleManagerArchiveModel::countAll();

        if($objConfig && $archives > 0 && $arrConfig = StringUtil::deserialize($objConfig->styleManager))
        {
            $key = array_key_first($arrConfig);

            if($key === StyleManager::VARS_KEY && count($arrConfig) > 1)
            {
                $key = array_keys($arrConfig)[1];
            }

            return is_numeric($key);
        }

        return false;
    }

    /**
     * Perform the object conversion
     */
    public function performObjectConversion($table = null): void
    {
        $arrTables = $this->Database->listTables();

        if(null === $table || !in_array($table, $arrTables))
        {
            return;
        }

        if($objRows = $this->Database->query("SELECT id, styleManager FROM " . $table . " WHERE styleManager IS NOT NULL"))
        {
            $objArchives = StyleManagerArchiveModel::findAll();
            $arrArchives = [];

            if(null === $objArchives)
            {
                return;
            }

            foreach ($objArchives as $objArchive)
            {
                $arrArchives[ $objArchive->id ] = $objArchive->identifier;
            }

            $arrConfigs = $objRows->fetchEach('styleManager');
            $arrIds = [];

            foreach ($arrConfigs as $sttConfig)
            {
                if($arrConfig = StringUtil::deserialize($sttConfig))
                {
                    $arrIds = array_merge($arrIds, array_keys($arrConfig));
                }
            }

            $objGroups = StyleManagerModel::findMultipleByIds($arrIds);
            $arrGroups = [];

            if(null !== $objGroups)
            {
                foreach ($objGroups as $objGroup)
                {
                    $arrGroups[ $objGroup->id ] = $objGroup;
                }

                foreach ($objRows->fetchAllAssoc() as $arrRow)
                {
                    $config = StringUtil::deserialize($arrRow['styleManager']);

                    $key = array_key_first($config);

                    if($key === StyleManager::VARS_KEY && count($config) > 1)
                    {
                        $key = array_keys($config)[1];
                    }

                    // Skip is config already converted
                    if(!is_numeric($key))
                    {
                        continue;
                    }

                    $arrAliasPairKeys = array_map(function($intGroupKey) use ($arrArchives, $arrGroups) {
                        return $intGroupKey === StyleManager::VARS_KEY ? StyleManager::VARS_KEY : StyleManager::generateAlias($arrArchives[ $arrGroups[$intGroupKey]->pid ], $arrGroups[$intGroupKey]->alias);
                    }, array_keys($config));

                    $newConfig = array_combine($arrAliasPairKeys, $config);

                    $this->Database
                        ->prepare("UPDATE " . $table . " SET styleManager=? WHERE id=?")
                        ->execute(
                            serialize($newConfig),
                            $arrRow['id']
                        );
                }
            }
        }
    }

    /**
     * Merge group objects
     */
    public static function mergeGroupObjects(?StyleManagerModel $objOriginal, ?StyleManagerModel $objMerge, ?array $skipFields = null, bool $skipEmpty = true, $forceOverwrite = true): ?StyleManagerModel
    {
        if(null === $objOriginal || null === $objMerge)
        {
            return $objOriginal;
        }

        Controller::loadDataContainer('tl_style_manager');

        foreach ($objMerge->row() as $field => $value)
        {
            if(
                ($skipEmpty && (!$value || strtolower($value) === 'null')) ||
                (null !== $skipFields && in_array($field, $skipFields))
            )
            {
                continue;
            }

            switch($field)
            {
                // Merge and manipulation of existing classes
                case 'cssClasses':
                    if($objOriginal->{$field})
                    {
                        $arrClasses = StringUtil::deserialize($objOriginal->{$field}, true);
                        $arrExists  = self::flattenKeyValueArray($arrClasses);
                        $arrValues  = StringUtil::deserialize($value, true);

                        foreach($arrValues as $cssClass)
                        {
                            if(array_key_exists($cssClass['key'], $arrExists))
                            {
                                if(!$forceOverwrite)
                                {
                                    continue;
                                }

                                // Overwrite existing value
                                $key = array_search($field, array_column($arrClasses, 'key'));

                                $arrClasses[ $key ] = [
                                    'key' => $cssClass['key'],
                                    'value' => $cssClass['value']
                                ];

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

                    if(isset($fieldOptions['eval']['multiple']) && !!$fieldOptions['eval']['multiple'] && $fieldOptions['inputType'] === 'checkbox')
                    {
                        $arrElements = StringUtil::deserialize($objOriginal->{$field}, true);
                        $arrValues   = StringUtil::deserialize($value, true);

                        foreach($arrValues as $element)
                        {
                            if(in_array($element, $arrElements))
                            {
                                if(!$forceOverwrite)
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
     */
    public function export(?DataContainer $dc, $objArchives = null, bool $blnSendToBrowser = true)
    {
        // Create a new XML document
        $xml = new DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Archives
        if(null === $objArchives)
        {
            $objArchives = StyleManagerArchiveModel::findAll(['order' => 'groupAlias,sorting']);
        }

        if (null === $objArchives)
        {
            Message::addError($GLOBALS['TL_LANG']['ERR']['noStyleManagerConfigFound']);
            self::redirect(self::getReferer());
        }

        // Root element
        $archives = $xml->createElement('archives');
        $archives = $xml->appendChild($archives);

        // Add the archives
        while($objArchives->next())
        {
            $this->addArchiveData($xml, $archives, $objArchives);
        }

        // Generate temp name
        $strTmp = md5(uniqid(mt_rand(), true));

        // Create file and open the "save as …" dialogue
        $objFile = new File('system/tmp/' . $strTmp);
        $objFile->write($xml->saveXML());
        $objFile->close();

        if(!$blnSendToBrowser)
        {
            return $objFile;
        }

        $objFile->sendToBrowser('style-manager-export.xml');
    }

    /**
     * Add an archive data row to the XML document
     */
    protected function addArchiveData(DOMDocument $xml, DOMNode $archives, Collection $objArchive): void
    {
        $this->loadDataContainer('tl_style_manager_archive');

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
     */
    protected function addChildrenData(DOMDocument $xml, DOMElement $archive, int $pid): void
    {
        // Add children node
        $children = $xml->createElement('children');
        $children = $archive->appendChild($children);

        $objChildren = StyleManagerModel::findByPid($pid);

        if($objChildren === null)
        {
            return;
        }

        $this->loadDataContainer('tl_style_manager');

        while($objChildren->next())
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

            $value = $xml->createTextNode($v);
            $field->appendChild($value);
        }
    }

    /**
     * Flatten Key Value Array
     */
    public static function flattenKeyValueArray($arr): array
    {
        if(empty($arr))
        {
            return $arr;
        }

        $arrTmp = array();

        foreach ($arr as $item) {
            $arrTmp[ $item['key'] ] = $item['value'];
        }

        return $arrTmp;
    }

    public static function combineAliases(...$aliases): string
    {
        return implode('_', $aliases);
    }
}
