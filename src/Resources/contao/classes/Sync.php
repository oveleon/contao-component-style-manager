<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\DataContainer;
use Contao\Environment;
use Contao\File;
use Contao\FileUpload;
use Contao\Input;
use Contao\Message;
use Contao\Model\Collection;
use Contao\StringUtil;
use Contao\System;

class Sync extends Backend
{
    /**
     * @var string
     */
    protected $strRootDir;

    /**
     * Set the root directory
     */
    public function __construct()
    {
        parent::__construct();
        $this->strRootDir = System::getContainer()->getParameter('kernel.project_dir');
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

        if($objConfig && $arrConfig = StringUtil::deserialize($objConfig->styleManager))
        {
            return is_numeric(array_key_first($arrConfig));
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

                    // Skip is config already converted
                    if(!is_numeric(array_key_first($config)))
                    {
                        continue;
                    }

                    $arrAliasPairKeys = array_map(function($intGroupKey) use ($arrArchives, $arrGroups) {
                        return StyleManager::generateAlias($arrArchives[ $arrGroups[$intGroupKey]->pid ], $arrGroups[$intGroupKey]->alias);
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
     * Display import form in back end
     *
     * @return string
     *
     * @throws \Exception
     */
    public function importStyleManager()
    {
        Config::set('uploadTypes', Config::get('uploadTypes') . ',xml');

        /** @var FileUpload $objUploader */
        $objUploader = new FileUpload();

        if (Input::post('FORM_SUBMIT') == 'tl_style_manager_import')
        {
            if (!Input::post('confirm'))
            {
                $arrUploaded = $objUploader->uploadTo('system/tmp');

                if (empty($arrUploaded))
                {
                    Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
                    $this->reload();
                }

                $arrFiles = array();

                foreach ($arrUploaded as $strFile)
                {
                    // Skip folders
                    if (is_dir($this->strRootDir . '/' . $strFile))
                    {
                        Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['importFolder'], basename($strFile)));
                        continue;
                    }

                    $objFile = new File($strFile);

                    // Skip anything but .xml files
                    if ($objFile->extension != 'xml')
                    {
                        Message::addError(sprintf($GLOBALS['TL_LANG']['ERR']['filetype'], $objFile->extension));
                        continue;
                    }

                    $arrFiles[] = $strFile;
                }
            }

            // Check whether there are any files
            if (empty($arrFiles))
            {
                Message::addError($GLOBALS['TL_LANG']['ERR']['all_fields']);
                $this->reload();
            }

            $this->importStyleManagerFile($arrFiles);
        }

        if (Input::post('FORM_SUBMIT') == 'tl_style_manager_import_bundle')
        {
            if($bundleFiles = Input::post('bundleFiles'))
            {
                $this->importStyleManagerFile(array_map(fn($n) => html_entity_decode($n), $bundleFiles));
            }
        }

        $template = new BackendTemplate('be_style_manager_import');

        $template->backUrl = StringUtil::ampersand(str_replace('&key=import', '', Environment::get('request')));
        $template->backBT = $GLOBALS['TL_LANG']['MSC']['backBT'];
        $template->backBTTitle = $GLOBALS['TL_LANG']['MSC']['backBTTitle'];

        $template->formId = 'tl_style_manager_import';
        $template->fileMaxSize = Config::get('maxFileSize');

        $template->labelSource = $GLOBALS['TL_LANG']['tl_style_manager_archive']['source'][0];
        $template->descSource = $GLOBALS['TL_LANG']['tl_style_manager_archive']['source'][1];
        $template->fieldUpload = $objUploader->generateMarkup();

        $template->labelImport = $GLOBALS['TL_LANG']['tl_style_manager_archive']['import'][0];
        $template->labelBundleConfig = $GLOBALS['TL_LANG']['tl_style_manager_archive']['headingBundleConfig'];
        $template->emptyBundleFiles = $GLOBALS['TL_LANG']['tl_style_manager_archive']['emptyBundleConfig'];
        $template->descBundleFiles = $GLOBALS['TL_LANG']['tl_style_manager_archive']['descBundleConfig'];

        $template->bundleFiles = \Oveleon\ContaoComponentStyleManager\Config::getBundleConfigurationFiles() ?? [];

        return $template->parse();
    }

    /**
     * Import StyleManager data from file
     *
     * @param array $arrFiles
     * @param bool $blnSave
     *
     * @throws \Exception
     */
    public function importStyleManagerFile(array $arrFiles, bool $blnSave = true): ?array
    {
        $arrStyleArchives = [];
        $arrStyleGroups = [];

        // Get the next id
        $intArchiveId  = $this->Database->getNextId('tl_style_manager_archive');
        $intGroupId  = $this->Database->getNextId('tl_style_manager');

        foreach ($arrFiles as $strFilePath)
        {
            // Open file
            $objFile = new File($strFilePath);

            // Check if file exists
            if ($objFile->exists())
            {
                // Load xml file
                $xml = new \DOMDocument();
                $xml->preserveWhiteSpace = false;
                $xml->loadXML($objFile->getContent());

                // Continue if there is no XML file
                if (!$xml instanceof \DOMDocument)
                {
                    Message::addError(sprintf($GLOBALS['TL_LANG']['tl_theme']['missing_xml'], basename($strFilePath)));
                    continue;
                }

                // Get archives node
                $archives = $xml->getElementsByTagName('archives');

                if($archives->count()){
                    // Skip archives node
                    $archives = $archives->item(0)->childNodes;
                }else return null;

                if($blnSave)
                {
                    // Lock the tables
                    $arrLocks = array
                    (
                        'tl_style_manager_archive' => 'WRITE',
                        'tl_style_manager'         => 'WRITE'
                    );

                    // Load the DCAs of the locked tables
                    foreach (array_keys($arrLocks) as $table)
                    {
                        $this->loadDataContainer($table);
                    }

                    $this->Database->lockTables($arrLocks);
                }

                // Check if archive exists
                $archiveExists = function (string $identifier) : bool
                {
                    return $this->Database->prepare("SELECT identifier FROM tl_style_manager_archive WHERE identifier=?")->execute($identifier)->numRows > 0;
                };

                // Check if children exists
                $childrenExists = function (string $alias, string $pid) : bool
                {
                    return $this->Database->prepare("SELECT alias FROM tl_style_manager WHERE alias=? AND pid=?")->execute($alias, $pid)->numRows > 0;
                };

                // Loop through the archives
                for ($i=0; $i<$archives->length; $i++)
                {
                    $archive = $archives->item($i)->childNodes;
                    $identifier = $archives->item($i)->getAttribute('identifier');

                    if(!$blnSave || !$archiveExists($identifier))
                    {
                        $objArchive = new StyleManagerArchiveModel();
                        $objArchive->id = ++$intArchiveId;
                    }
                    else
                    {
                        $objArchive = StyleManagerArchiveModel::findByIdentifier($identifier);
                    }

                    // Loop through the archives fields
                    for ($a=0; $a<$archive->length; $a++)
                    {
                        $strField = $archive->item($a)->nodeName;

                        if($strField === 'field')
                        {
                            $strName  = $archive->item($a)->getAttribute('title');
                            $strValue = $archive->item($a)->nodeValue;

                            if($strName === 'id' || strtolower($strValue) === 'null')
                            {
                                continue;
                            }

                            $objArchive->{$strName} = $strValue;
                        }
                        elseif($strField === 'children')
                        {
                            $children = $archive->item($a)->childNodes;

                            // Loop through the archives fields
                            for ($c=0; $c<$children->length; $c++)
                            {
                                $alias = $children->item($c)->getAttribute('alias');
                                $fields = $children->item($c)->childNodes;

                                if(!$blnSave || !$childrenExists($alias, $objArchive->id))
                                {
                                    $objChildren = new StyleManagerModel();
                                    $objChildren->id = ++$intGroupId;
                                }
                                else
                                {
                                    $objChildren = StyleManagerModel::findByAliasAndPid($alias, $objArchive->id);
                                }

                                // Loop through the children fields
                                for ($f=0; $f<$fields->length; $f++)
                                {
                                    $strName = $fields->item($f)->getAttribute('title');
                                    $strValue = $fields->item($f)->nodeValue;

                                    if($strName === 'id' || !$strValue || strtolower($strValue) === 'null')
                                    {
                                        continue;
                                    }

                                    switch($strName)
                                    {
                                        case 'pid':
                                            $strValue = $objArchive->id;
                                            break;
                                        case 'cssClasses':
                                            if($objChildren->{$strName})
                                            {
                                                $arrClasses = StringUtil::deserialize($objChildren->{$strName}, true);
                                                $arrExists  = $this->flattenKeyValueArray($arrClasses);
                                                $arrValues  = StringUtil::deserialize($strValue, true);

                                                foreach($arrValues as $cssClass)
                                                {
                                                    if(array_key_exists($cssClass['key'], $arrExists))
                                                    {
                                                        continue;
                                                    }

                                                    $arrClasses[] = array(
                                                        'key' => $cssClass['key'],
                                                        'value' => $cssClass['value']
                                                    );
                                                }

                                                $strValue  = serialize($arrClasses);
                                            }

                                            break;
                                        default:
                                            $dcaField = $GLOBALS['TL_DCA']['tl_style_manager']['fields'][$strName];

                                            if(isset($dcaField['eval']['multiple']) && !!$dcaField['eval']['multiple'] && $dcaField['inputType'] === 'checkbox')
                                            {
                                                $arrElements = StringUtil::deserialize($objChildren->{$strName}, true);
                                                $arrValues   = StringUtil::deserialize($strValue, true);

                                                foreach($arrValues as $element)
                                                {
                                                    if(in_array($element, $arrElements))
                                                    {
                                                        continue;
                                                    }

                                                    $arrElements[] = $element;
                                                }

                                                $strValue  = serialize($arrElements);
                                            }
                                    }

                                    $objChildren->{$strName} = $strValue;
                                }

                                // Save children data
                                if($blnSave)
                                {
                                    $objChildren->save();
                                }
                                else
                                {
                                    $arrStyleGroups[] = $objChildren->current();
                                }
                            }
                        }
                    }

                    // Save archive data
                    if($blnSave)
                    {
                        $objArchive->save();
                    }
                    else
                    {
                        $arrStyleArchives[] = $objArchive->current();
                    }
                }

                // Unlock the tables
                if($blnSave)
                {
                    $this->Database->unlockTables();
                    Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['MSC']['styleManagerConfigImported'], basename($strFilePath)));
                }
            }
        }

        if($blnSave)
        {
            return null;
        }

        return [$arrStyleArchives, $arrStyleGroups];
    }

    /**
     * Export StyleManager data
     *
     * @param DataContainer $dc
     *
     * @throws \Exception
     */
    public function exportStyleManager(DataContainer $dc)
    {
        // Create a new XML document
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;

        // Archives
        $objArchive = StyleManagerArchiveModel::findAll(['order' => 'groupAlias,sorting']);

        if (null === $objArchive)
        {
            Message::addError($GLOBALS['TL_LANG']['ERR']['noStyleManagerConfigFound']);
            self::redirect(self::getReferer());
        }

        // Root element
        $archives = $xml->createElement('archives');
        $archives = $xml->appendChild($archives);

        // Add the archives
        while($objArchive->next())
        {
            $this->addArchiveData($xml, $archives, $objArchive);
        }

        // Generate temp name
        $strTmp = md5(uniqid(mt_rand(), true));

        // Create file and open the "save as …" dialogue
        $objFile = new File('system/tmp/' . $strTmp);
        $objFile->write($xml->saveXML());
        $objFile->close();

        $objFile->sendToBrowser('style-manager-export.xml');
    }

    /**
     * Add an archive data row to the XML document
     *
     * @param \DOMDocument $xml
     * @param \DOMNode $archives
     * @param Collection $objArchive
     */
    protected function addArchiveData(\DOMDocument $xml, \DOMNode $archives, Collection $objArchive)
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
     *
     * @param \DOMDocument $xml
     * @param \DOMNode|\DOMElement $archive
     * @param int $pid
     */
    protected function addChildrenData(\DOMDocument $xml, \DOMElement $archive, int $pid)
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
     *
     * @param \DOMDocument $xml
     * @param \DOMNode $row
     * @param array $arrData
     */
    protected function addRowData(\DOMDocument $xml, \DOMNode $row, array $arrData)
    {
        foreach ($arrData as $k=>$v)
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
     *
     * @param $arr
     *
     * @return array
     */
    public function flattenKeyValueArray($arr)
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
}
