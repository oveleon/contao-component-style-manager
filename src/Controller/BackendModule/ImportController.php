<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Controller\BackendModule;

use Contao\Config;
use Contao\Controller;
use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\Database;
use Contao\File;
use Contao\FileUpload;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use DOMDocument;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManager\Config as BundleConfig;
use Oveleon\ContaoComponentStyleManager\StyleManager\Sync;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route(path: '%contao.backend.route_prefix%/style-manager-import', name: ImportController::class, defaults: ['_scope' => 'backend'])]
class ImportController extends AbstractBackendController
{
    public const ROUTE = 'style-manager-import';

    public function __construct(
        protected RequestStack $requestStack,
        protected TranslatorInterface $translator,
        private readonly ContaoCsrfTokenManager $tokenManager,
    ) {
    }

    public function __invoke(): Response
    {
        Controller::loadLanguageFile('defaults');
        Controller::loadLanguageFile('tl_style_manager_import');

        // Add xml extension as valid upload type
        Config::set('uploadTypes', Config::get('uploadTypes') . ',xml');

        $objUploader = new FileUpload();

        // Get current request
        $request = $this->requestStack->getCurrentRequest();

        $partial = !!$request->get('import_partial');
        $configs = null;

        switch ($request->get('FORM_SUBMIT'))
        {
            case 'style_manager_import':
                $configs = $this->importByFileUploader($objUploader, $partial);
                break;

            case 'style_manager_import_partial':
                $this->importPartial($request->get('files'), $request->get('archives'), $request->get('groups'));
                break;

            case 'style_manager_import_bundle':
                if(!$files = $request->get('bundleFiles'))
                {
                    Message::addError($this->translator->trans('ERR.all_fields', [], 'contao_default'));

                    // Reload page
                    throw new RedirectResponseException($request->getUri(), 303);
                }

                $configs = $this->importBundleConfigFiles($files, $partial);
        }

        if($configs !== null)
        {
            $configs = $this->createImportTree(...$configs);
        }

        return $this->render('@Contao/import.html.twig', [
            'headline'        => $partial ? $this->translator->trans('tl_style_manager_import.importPartial', [], 'contao_default') : 'Import',
            'messages'        => Message::generate(),
            'useBundleConfig' => System::getContainer()->getParameter('contao_component_style_manager.use_bundle_config'),
            'bundleFiles'     => BundleConfig::getBundleConfigurationFiles() ?? [],
            'partial'         => $partial,
            'configs'         => $configs,
            'form'            => [
                'id'                   => 'style_manager_import',
                'rt'                   => $this->tokenManager->getDefaultTokenValue(),
                'maxFileSize'          => Config::get('maxFileSize'),
                'uploadWidget'         => $objUploader->generateMarkup()
            ],
            'action'          => [
                'back'                 => str_replace('/' . self::ROUTE, '', $this->generateUrl('contao_backend')) . '?do=style_manager',
            ],
            'label'           => [
                'back'                 => $this->translator->trans('MSC.backBT', [], 'contao_default'),
                'backTitle'            => $this->translator->trans('MSC.backBTTitle', [], 'contao_default'),
                'files'                => $this->translator->trans('tl_style_manager_import.files', [], 'contao_default'),
                'import'               => $this->translator->trans('tl_style_manager_import.import', [], 'contao_default'),
                'importPartial'        => $this->translator->trans('tl_style_manager_import.importPartial', [], 'contao_default'),
                'importPartialDesc'    => $this->translator->trans('tl_style_manager_import.importPartialDesc', [], 'contao_default'),
                'importManual'         => $this->translator->trans('tl_style_manager_import.importManual', [], 'contao_default'),
                'importExplanation'    => $this->translator->trans('tl_style_manager_import.importExplanation', [], 'contao_default'),
                'widgetDescription'    => $this->translator->trans('tl_style_manager_import.uploadFile', [], 'contao_default'),
                'bundleUpload'         => $this->translator->trans('tl_style_manager_import.bundleConfig', [], 'contao_default'),
                'bundleConfigEmpty'    => $this->translator->trans('tl_style_manager_import.bundleConfigEmpty', [], 'contao_default'),
                'bundleConfigInactive' => $this->translator->trans('tl_style_manager_import.bundleConfigInactive', [], 'contao_default'),
            ]
        ]);
    }

    /**
     * Create a tree from archives and groups
     */
    public function createImportTree($archives, $groups, $files): array
    {
        $collection = [
            'files' => $files,
            'collection' => []
        ];

        $_groups = $groups;

        // Check if archive exists
        $getGroups = function (int $pid) use (&$_groups): array
        {
            $collection = [];

            foreach ($_groups ?? [] as $alias => &$group)
            {
                if($pid === $group->pid)
                {
                    $collection[ $alias ] = $group;

                    // If nothing was found, cast strings and false to 0
                    $offset = (int) array_search($alias, $_groups);

                    array_splice($_groups, $offset, 1);
                }
            }

            return $collection;
        };

        foreach ($archives ?? [] as $key => $archive)
        {
            $collection['collection'][ $key ] = [
                'archive'  => $archive,
                'children' => $getGroups((int) $archive->id)
            ];
        }

        return $collection;
    }

    /**
     * Partial import
     */
    private function importPartial(array $files, array $archives, array $groups): ?array
    {
        return $this->importFiles($files, true, $archives, $groups);
    }

    /**
     * Import based on bundle configurations
     */
    public function importBundleConfigFiles(array $files, bool $partial = false): ?array
    {
        return $this->importFiles(array_map(fn($n) => html_entity_decode($n), $files), !$partial);
    }

    /**
     * Import using a FileUploader
     */
    public function importByFileUploader(FileUpload $objUploader, bool $partial = false): ?array
    {
        Controller::loadLanguageFile('default');

        // Get current request
        $request = $this->requestStack->getCurrentRequest();

        // Upload files
        $uploaded = $objUploader->uploadTo('system/tmp');

        // Get root dir
        /** @var string $rootDir */
        $rootDir = System::getContainer()->getParameter('kernel.project_dir');

        if (empty($uploaded))
        {
            Message::addError($this->translator->trans('ERR.all_fields', [], 'contao_default'));

            // Reload page
            throw new RedirectResponseException($request->getUri(), 303);
        }

        $arrFiles = [];

        foreach ($uploaded as $file)
        {
            // Skip folders
            if (is_dir($rootDir . '/' . $file))
            {
                Message::addError(sprintf($this->translator->trans('ERR.importFolder', [], 'contao_default'), basename($file)));
                continue;
            }

            $objFile = new File($file);

            // Skip anything but .xml files
            if ($objFile->extension != 'xml')
            {
                Message::addError(sprintf($this->translator->trans('ERR.filetype', [], 'contao_default'), $objFile->extension));
                continue;
            }

            $arrFiles[] = $file;
        }

        // Check whether there are any files
        if (empty($arrFiles))
        {
            Message::addError($this->translator->trans('ERR.all_fields', [], 'contao_default'));

            // Reload page
            throw new RedirectResponseException($request->getUri(), 303);
        }

        $data = $this->importFiles($arrFiles, !$partial);

        if($partial)
        {
            return $data;
        }

        // Reload page
        throw new RedirectResponseException($request->getUri(), 303);
    }

    /**
     * Import config files
     */
    public static function importFiles(array $files, bool $blnSave = true, ?array $allowedArchives = null, ?array $allowedGroups = null): ?array
    {
        $arrStyleArchives = [];
        $arrStyleGroups = [];

        $database = Database::getInstance();
        $translator = Controller::getContainer()->get('translator');

        // Get the next id
        $intArchiveId  = $database->getNextId('tl_style_manager_archive');
        $intGroupId  = $database->getNextId('tl_style_manager');

        foreach ($files as $filePath)
        {
            $objFile = new File($filePath);

            if (!$objFile->exists())
            {
                if (!is_file($filePath) || !is_readable($filePath))
                {
                    continue;
                }

                $content = file_get_contents($filePath);

                if (false === $content)
                {
                    continue;
                }
            }
            else
            {
                $content = $objFile->getContent();
            }

            // Load xml file
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = false;

            // Continue if there is no readable XML file
            if (!$xml->loadXML($content))
            {
                Message::addError($translator?->trans('tl_theme.missing_xml', [basename($filePath)], 'contao_default'));
                continue;
            }

            // Get archives node
            $archives = $xml->getElementsByTagName('archives');

            if($archives->count()){
                // Skip archives node
                $archives = $archives->item(0)->childNodes;
            }else return null;

            // Lock the tables
            $arrLocks = array
            (
                'tl_style_manager_archive' => 'WRITE',
                'tl_style_manager'         => 'WRITE'
            );

            // Load the DCAs of the locked tables
            foreach (array_keys($arrLocks) as $table)
            {
                Controller::loadDataContainer($table);
            }

            if($blnSave)
            {
                $database->lockTables($arrLocks);
            }

            // Check if archive exists
            $archiveExists = function (string $identifier) use ($database, $blnSave, $arrStyleArchives) : bool
            {
                if(!$blnSave)
                {
                    return array_key_exists($identifier, $arrStyleArchives);
                }

                return $database->prepare("SELECT identifier FROM tl_style_manager_archive WHERE identifier=?")->execute($identifier)->numRows > 0;
            };

            // Check if children exist
            $childrenExists = function (string $alias, int $pid) use($database, $blnSave, $arrStyleGroups) : bool
            {
                if(!$blnSave)
                {
                    return array_key_exists($alias, $arrStyleGroups);
                }

                return $database->prepare("SELECT alias FROM tl_style_manager WHERE alias=? AND pid=?")->execute($alias, $pid)->numRows > 0;
            };

            // Loop through the archives
            for ($i=0; $i<$archives->length; $i++)
            {
                $archive = $archives->item($i)->childNodes;
                $identifier = $archives->item($i)->getAttribute('identifier');

                if(null !== $allowedArchives && !in_array($identifier, $allowedArchives))
                {
                    continue;
                }

                if(!$blnSave || !$archiveExists($identifier))
                {
                    if(!$blnSave && $archiveExists($identifier))
                    {
                        $objArchive = $arrStyleArchives[$identifier];
                    }
                    else
                    {
                        $objArchive = new StyleManagerArchiveModel();
                        $objArchive->id = ++$intArchiveId;
                        $objArchive->tstamp = time();
                    }
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
                        $strValue = $archive->item($a)->nodeValue ?? '';

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

                            $strChildAlias = Sync::combineAliases($objArchive->identifier, $alias);

                            if(null !== $allowedGroups && !in_array($strChildAlias, $allowedGroups))
                            {
                                continue;
                            }

                            if(!$blnSave || !$childrenExists($strChildAlias, $objArchive->id))
                            {
                                if(!$blnSave && $childrenExists($strChildAlias, $objArchive->id))
                                {
                                    $objChildren = $arrStyleGroups[$strChildAlias];
                                }
                                else
                                {
                                    $objChildren = new StyleManagerModel();
                                    $objChildren->id = ++$intGroupId;
                                    $objChildren->tstamp = time();
                                }
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
                                            /** @var array<array<int|string>> $arrClasses */
                                            $arrClasses = StringUtil::deserialize($objChildren->{$strName}, true);
                                            $arrExists  = Sync::flattenKeyValueArray($arrClasses);

                                            /** @var array<array<int|string>> $arrValues */
                                            $arrValues = StringUtil::deserialize($strValue, true);

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
                                            /** @var array<array<int|string>> $arrElements */
                                            $arrElements = StringUtil::deserialize($objChildren->{$strName}, true);
                                            /** @var array<array<int|string>> $arrValues */
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
                                $strKey = Sync::combineAliases($objArchive->identifier, $objChildren->alias);
                                $arrStyleGroups[ $strKey ] = $objChildren->current();
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
                    $arrStyleArchives[ $objArchive->identifier ] = $objArchive->current();
                }
            }

            // Unlock the tables
            if($blnSave)
            {
                $database->unlockTables();
                Message::addConfirmation($translator->trans('MSC.styleManagerConfigImported', [basename($filePath)], 'contao_default'));
            }
        }

        if ($blnSave)
        {
            return null;
        }

        return [$arrStyleArchives, $arrStyleGroups, $files];
    }
}
