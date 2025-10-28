<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Widget;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Database;
use Contao\DC_Table;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\Event\StyleManagerSkipFieldEvent;
use Oveleon\ContaoComponentStyleManager\EventListener\DataContainer\StyleManagerWidgetListener;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\Style\StyleGroup;
use Oveleon\ContaoComponentStyleManager\Util\StyleManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

/**
 * Provide methods to handle select menus for style manager.
 *
 * @property bool  $mandatory
 * @property int   $size
 * @property bool  $multiple
 * @property array $options
 * @property bool  $chosen
 *
 * @internal
 */
class ComponentStyleSelect extends Widget
{
    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    public RequestStack|null $requestStack {
        get => System::getContainer()->get('request_stack');
    }

    public Environment|null $twig {
        get => System::getContainer()->get('twig');
    }

    public EventDispatcherInterface|null $eventDispatcher {
        get => System::getContainer()->get('event_dispatcher');
    }

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);
    }

    /**
     * Generate the widget and return it as string
     *
     * @return string
     */
    public function generate()
    {
        $arrObjStyleArchives = StyleManagerArchiveModel::findAllWithConfiguration(['order'=>'sorting']);
        $arrObjStyleGroups   = StyleManagerModel::findByTableAndConfiguration($this->strTable, ['order'=>'pid,sorting']);

        if ($arrObjStyleGroups === null || $arrObjStyleArchives === null)
        {
            return $this->twig?->render('@Contao/backend/widget/style_manager.html.twig', ['empty' => true]);
        }

        $isEmpty = true;
        $arrCollection = $arrArchives = $arrOrder = $arrParentMapping = [];

        // Prepare archives
        foreach ($arrObjStyleArchives as $objStyleArchive)
        {
            $arrArchives[$objStyleArchive->identifier] = [
                'title'      => $objStyleArchive->title,
                'identifier' => $objStyleArchive->identifier,
                'desc'       => $objStyleArchive->desc,
                'group'      => $objStyleArchive->groupAlias,
                'model'      => $objStyleArchive
           ];

            $arrParentMapping[$objStyleArchive->id ?? $objStyleArchive->identifier] = $objStyleArchive->identifier;

            $arrOrder[] = $objStyleArchive->identifier;
        }

        // Restore default values
        $this->varValue = StyleManager::deserializeValues($this->varValue);

        /**
         * @var StyleGroup|StyleManagerModel $objStyleGroup
         */
        foreach ($arrObjStyleGroups as $objStyleGroup)
        {
            $arrOptions      = [];
            $strClass        = 'tl_select';
            $arrFieldOptions = [];

            // set blank option
            if (!!$objStyleGroup->blankOption)
            {
                $arrFieldOptions[] = ['value'=>'', 'label'=>'-'];
            }

            // skip specific content elements
            if (!!$objStyleGroup->extendContentElement && $this->strTable === 'tl_content')
            {
                /** @var array $arrContentElements */
                $arrContentElements = StringUtil::deserialize($objStyleGroup->contentElements, true);

                if ($arrContentElements !== null && !\in_array($this->activeRecord->type, $arrContentElements))
                {
                    continue;
                }
            }

            // skip specific form fields
            if (!!$objStyleGroup->extendFormFields && $this->strTable === 'tl_form_field')
            {
                $arrFormFields = StringUtil::deserialize($objStyleGroup->formFields);

                if ($arrFormFields !== null && !\in_array($this->activeRecord->type, $arrFormFields))
                {
                    continue;
                }
            }

            // skip specific modules
            if (!!$objStyleGroup->extendModule && $this->strTable === 'tl_module')
            {
                $arrModules = StringUtil::deserialize($objStyleGroup->modules);

                if ($arrModules !== null && !\in_array($this->activeRecord->type, $arrModules))
                {
                    continue;
                }
            }

            $event = new StyleManagerSkipFieldEvent($objStyleGroup, $this);
            $this->eventDispatcher->dispatch($event);

            if ($event->shouldSkip())
            {
                continue;
            }

            $opts = StringUtil::deserialize($objStyleGroup->cssClasses);

            foreach ($opts as $opt)
            {
                $arrFieldOptions[] = [
                    'label' => $opt['value'] ?: $opt['key'],
                    'value' => $opt['key']
               ];
            }

            if (false === StyleManager::styleGroupMappableToArchives($objStyleGroup, $arrParentMapping))
            {
                continue;
            }

            $archiveIdent = $arrParentMapping[$objStyleGroup->pid];
            $strId        = StyleManager::generateAlias($arrArchives[$archiveIdent]['identifier'], $objStyleGroup->alias);
            $strFieldId   = $this->strId . '_' . $strId;
            $strFieldName = $this->strName . '[' . $strId . ']';

            foreach ($arrFieldOptions as $strKey=>$arrOption)
            {
                if (isset($arrOption['value']))
                {
                    $arrOptions[] = sprintf('<option value="%s"%s>%s</option>',
                        StringUtil::specialchars($arrOption['value']),
                        static::optionSelected($arrOption['value'], $this->varValue[$strId] ?? ''),
                        $arrOption['label']);
                }
                else
                {
                    $arrOptGroups = [];

                    foreach ($arrOption as $arrOptgroup)
                    {
                        $arrOptGroups[] = sprintf('<option value="%s"%s>%s</option>',
                            StringUtil::specialchars($arrOptgroup['value']),
                            static::optionSelected($arrOption['value'], $this->varValue[$strId] ?? ''),
                            $arrOptgroup['label']);
                    }

                    $arrOptions[] = sprintf('<optgroup label="&nbsp;%s">%s</optgroup>', StringUtil::specialchars($strKey), implode('', $arrOptGroups));
                }
            }

            // add chosen
            if (!!$objStyleGroup->chosen)
            {
                $strClass .= ' tl_chosen';
            }

            // create a collection
            $groupAlias      = ($arrArchives[$archiveIdent]['group'] ?: 'group-' . $arrArchives[$archiveIdent]['identifier']) . '-' . $this->id;
            $collectionAlias = $arrArchives[$archiveIdent]['identifier'];

            if (!\in_array($collectionAlias, array_keys($arrCollection)))
            {
                $arrCollection[$collectionAlias] = [
                    'label'      => $arrArchives[$archiveIdent]['title'],
                    'desc'       => $arrArchives[$archiveIdent]['desc'] ?? '',
                    'group'      => $groupAlias,
                    'groupTitle' => $arrArchives[$archiveIdent]['group'] ?? null,
                    'fields'     => []
               ];
            }

            $arrCollection[$collectionAlias]['fields'][] = sprintf('%s%s<select name="%s" id="ctrl_%s" class="%s%s"%s data-action="focus->contao--scroll-offset#store">%s</select>%s%s',
                (str_contains($objStyleGroup->cssClass ?? '', 'separator') ? '<hr>' : '') . '<div' . ($objStyleGroup->cssClass ? ' class="' . $objStyleGroup->cssClass . '"' : ''). (!!$objStyleGroup->chosen ? ' data-controller="contao--choices"' : '') .'>',
                '<h3><label for="ctrl_' . $strFieldId . '">' . $objStyleGroup->title . '</label></h3>',
                $strFieldName,
                $strFieldId,
                $strClass,
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                $this->getAttributes(),
                implode('', $arrOptions),
                $this->wizard,
                '<p class="tl_help' . ($objStyleGroup->description ? ' tl_tip' : '') . '" title="">'.$objStyleGroup->description.'</p></div>'
            );

            $isEmpty = false;
        }

        if ($isEmpty)
        {
            return $this->twig?->render('@Contao/backend/widget/style_manager.html.twig', ['empty' => true]);
        }

        $objSession = $this->requestStack?->getSession()->getBag('contao_backend');
        $arrSession = $objSession?->get('stylemanager_section_states');

        $arrGroups = [];

        // sort collection by sort-index
        uksort($arrCollection, function($key1, $key2) use ($arrOrder) {
            return (array_search($key1, $arrOrder) > array_search($key2, $arrOrder));
        });

        // collect groups
        foreach ($arrCollection as $alias => $collection)
        {
            $arrGroups[$collection['group']][$alias] = $collection;
        }

        return $this->twig?->render('@Contao/backend/widget/style_manager.html.twig', [
            'id' => $this->id,
            'groups' => $arrGroups,
            'session' => $arrSession,
       ]);
    }

    /**
     * Check for a valid option and prepare template variables
     */
    public function validate()
    {
        $this->varValue = $this->getPost($this->strName);

        if ($this->varValue === null)
        {
            return;
        }

        if ($arrValue = StyleManager::serializeValues($this->varValue, $this->strTable))
        {
            $this->varValue = $arrValue;
        }

        $field   = self::getClassFieldNameByTable($this->strTable);
        $objUser = BackendUser::getInstance();

        // Update CSS class fields in case of multiple editing, or if a user has no rights for the field
        if ($field && (Input::get('act') === 'editAll' || !$objUser->hasAccess($this->strTable . '::' . $field, 'alexf')))
        {
            $dc = new DC_Table($this->strTable);
            $dc->field = $field;
            $dc->activeRecord = $this->activeRecord;

            // ToDo:
            $value = StyleManagerWidgetListener::resetClasses($this->activeRecord->{$field}, $dc, $this->strTable);
            $value = StyleManagerWidgetListener::updateClasses($value, $dc);

            // Update CSS class field
            Database::getInstance()->prepare('UPDATE ' . $this->strTable . ' SET ' . $field . '=? WHERE id=?')
                ->execute($value, $this->activeRecord->id);
        }
    }

    private function getClassFieldNameByTable(string $strTable): mixed
    {
        Backend::loadDataContainer($strTable);

        foreach (StyleManager::$validCssClassFields as $field => $size)
        {
            if (isset($GLOBALS['TL_DCA'][$strTable]['fields'][$field]))
            {
                return $field;
            }
        }

        return false;
    }
}
