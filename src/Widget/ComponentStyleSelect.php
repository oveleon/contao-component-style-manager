<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Widget;

use Contao\BackendUser;
use Contao\CoreBundle\Csrf\ContaoCsrfTokenManager;
use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\Database;
use Contao\DC_Table;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
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
    /**
     * Submit user input
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Template
     * @var string
     */
    protected $strTemplate = 'be_widget';

    private readonly ContaoCsrfTokenManager|null $tokenManager;

    private readonly InsertTagParser|null $insertTagParser;

    private readonly RequestStack|null $requestStack;

    private readonly Environment|null $twig;

    private readonly bool $showGroupTitle;

    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);

        $container = System::getContainer();

        $this->tokenManager = $container->get('contao.csrf.token_manager');
        $this->insertTagParser = $container->get('contao.insert_tag.parser');
        $this->requestStack = $container->get('request_stack');
        $this->twig = $container->get('twig');

        $this->showGroupTitle = (bool) $container->getParameter('contao_component_style_manager.show_group_title');
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
            return $this->renderEmptyMessage();
        }

        $isEmpty = true;
        $arrCollection = $arrArchives = $arrOrder = $arrParentMapping = [];

        // Prepare archives
        foreach ($arrObjStyleArchives as $objStyleArchive)
        {
            $arrArchives[ $objStyleArchive->identifier ] = [
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

        // Prepare group fields
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

            // skip third-party fields
            if (isset($GLOBALS['TL_HOOKS']['styleManagerSkipField']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerSkipField']))
            {
                foreach ($GLOBALS['TL_HOOKS']['styleManagerSkipField'] as $callback)
                {
                    if (System::importStatic($callback[0])->{$callback[1]}($objStyleGroup, $this))
                    {
                        continue 2;
                    }
                }
            }

            $opts = StringUtil::deserialize($objStyleGroup->cssClasses);

            foreach ($opts as $opt)
            {
                $arrFieldOptions[] = [
                    'label' => $opt['value'] ?: $opt['key'],
                    'value' => $opt['key']
                ];
            }

            // dynamically change or expand group options
            if (isset($GLOBALS['TL_HOOKS']['styleManagerGroupFieldOptions']) && \is_array($GLOBALS['TL_HOOKS']['styleManagerGroupFieldOptions']))
            {
                foreach ($GLOBALS['TL_HOOKS']['styleManagerGroupFieldOptions'] as $callback)
                {
                    if ($optionCallback = System::importStatic($callback[0])->{$callback[1]}($arrFieldOptions, $objStyleGroup, $this))
                    {
                        $arrFieldOptions = $optionCallback;
                    }
                }
            }

            if (false === StyleManager::styleGroupMappableToArchives($objStyleGroup, $arrParentMapping))
            {
                continue;
            }

            $archiveIdent = $arrParentMapping[$objStyleGroup->pid];
            $strId        = StyleManager::generateAlias($arrArchives[ $archiveIdent ]['identifier'], $objStyleGroup->alias);
            $strFieldId   = $this->strId . '_' . $strId;
            $strFieldName = $this->strName . '[' . $strId . ']';

            foreach ($arrFieldOptions as $strKey=>$arrOption)
            {
                if (isset($arrOption['value']))
                {
                    $arrOptions[] = sprintf('<option value="%s"%s>%s</option>',
                        StringUtil::specialchars($arrOption['value']),
                        static::optionSelected($arrOption['value'], $this->varValue[ $strId ] ?? ''),
                        $arrOption['label']);
                }
                else
                {
                    $arrOptGroups = [];

                    foreach ($arrOption as $arrOptgroup)
                    {
                        $arrOptGroups[] = sprintf('<option value="%s"%s>%s</option>',
                            StringUtil::specialchars($arrOptgroup['value']),
                            static::optionSelected($arrOption['value'], $this->varValue[ $strId ] ?? ''),
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
            $groupAlias      = ($arrArchives[ $archiveIdent ]['group'] ?: 'group-' . $arrArchives[ $archiveIdent ]['identifier']) . '-' . $this->id;
            $collectionAlias = $arrArchives[ $archiveIdent ]['identifier'];

            if (!\in_array($collectionAlias, array_keys($arrCollection)))
            {
                $arrCollection[ $collectionAlias ] = [
                    'label'      => $arrArchives[ $archiveIdent ]['title'],
                    'desc'       => $this->insertTagParser?->replaceInline(nl2br($arrArchives[ $archiveIdent ]['desc'] ?? '')),
                    'group'      => $groupAlias,
                    'groupTitle' => $arrArchives[ $archiveIdent ]['group'] ?? null,
                    'fields'     => []
                ];
            }

            $arrCollection[ $collectionAlias ]['fields'][] = sprintf('%s%s<select name="%s" id="ctrl_%s" class="%s%s"%s data-action="focus->contao--scroll-offset#store">%s</select>%s%s',
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
            return $this->renderEmptyMessage();
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
            $arrGroups[ $collection['group'] ][ $alias ] = $collection;
        }

        return $this->twig?->render('@Contao/backend/widget/stylemanager.html.twig', [
            'id'             => $this->id,
            'groups'         => $arrGroups,
            'showGroupTitle' => $this->showGroupTitle,
            'requestToken'   => $this->tokenManager?->getDefaultTokenValue(),
            'session'        => $arrSession,
        ]);
    }

    /**
     * Return the empty message
     */
    private function renderEmptyMessage(): string
    {
        System::loadLanguageFile('tl_style_manager');
        return '<div class="no_styles tl_info"><p>' . $GLOBALS['TL_LANG']['tl_style_manager']['noStylesDefined'] . '</p></div>';
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

        $field   = StyleManager::getClassFieldNameByTable($this->strTable);
        $objUser = BackendUser::getInstance();

        // Update CSS class fields in case of multiple editing, or if a user has no rights for the field
        if ($field && (Input::get('act') === 'editAll' || !$objUser->hasAccess($this->strTable . '::' . $field, 'alexf')))
        {
            $dc = new DC_Table($this->strTable);
            $dc->field = $field;
            $dc->activeRecord = $this->activeRecord;

            $value = StyleManager::resetClasses($this->activeRecord->{$field}, $dc, $this->strTable);
            $value = StyleManager::updateClasses($value, $dc);

            // Update CSS class field
            Database::getInstance()->prepare('UPDATE ' . $this->strTable . ' SET ' . $field . '=? WHERE id=?')
                ->execute($value, $this->activeRecord->id);
        }
    }
}
