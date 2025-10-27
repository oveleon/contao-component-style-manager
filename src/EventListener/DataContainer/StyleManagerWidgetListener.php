<?php

namespace Oveleon\ContaoComponentStyleManager\EventListener\DataContainer;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\StringUtil;
use Contao\System;
use Oveleon\ContaoComponentStyleManager\Event\AddStyleManagerPaletteEvent;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;
use Oveleon\ContaoComponentStyleManager\Util\StyleManager;

class StyleManagerWidgetListener
{
    #[AsCallback(table: 'tl_content', target: 'fields.cssID.load')]
    #[AsCallback(table: 'tl_module', target: 'fields.cssID.load')]
    #[AsCallback(table: 'tl_article', target: 'fields.cssID.load')]
    #[AsCallback(table: 'tl_layout', target: 'fields.cssClass.load')]
    #[AsCallback(table: 'tl_page', target: 'fields.cssClass.load')]
    #[AsCallback(table: 'tl_news', target: 'fields.cssClass.load')]
    #[AsCallback(table: 'tl_calendar_events', target: 'fields.cssClass.load')]
    #[AsCallback(table: 'tl_form', target: 'fields.attributes.load')]
    #[AsCallback(table: 'tl_form_field', target: 'fields.class.load')]
    public function onLoad(mixed $varValue, DataContainer $dc): mixed
    {
        return self::clearClasses($varValue, $dc);
    }

    #[AsCallback(table: 'tl_content', target: 'fields.cssID.save')]
    #[AsCallback(table: 'tl_module', target: 'fields.cssID.save')]
    #[AsCallback(table: 'tl_article', target: 'fields.cssID.save')]
    #[AsCallback(table: 'tl_layout', target: 'fields.cssClass.save')]
    #[AsCallback(table: 'tl_page', target: 'fields.cssClass.save')]
    #[AsCallback(table: 'tl_news', target: 'fields.cssClass.save')]
    #[AsCallback(table: 'tl_calendar_events', target: 'fields.cssClass.save')]
    #[AsCallback(table: 'tl_form', target: 'fields.attributes.save')]
    #[AsCallback(table: 'tl_form_field', target: 'fields.class.save')]
    public function onSave(mixed $varValue, DataContainer $dc): mixed
    {
        return self::updateClasses($varValue, $dc);
    }

    #[AsCallback(table: 'tl_content', target: 'config.onload')]
    #[AsCallback(table: 'tl_module', target: 'config.onload')]
    #[AsCallback(table: 'tl_article', target: 'config.onload')]
    #[AsCallback(table: 'tl_layout', target: 'config.onload')]
    #[AsCallback(table: 'tl_page', target: 'config.onload')]
    #[AsCallback(table: 'tl_news', target: 'config.onload')]
    #[AsCallback(table: 'tl_calendar_events', target: 'config.onload')]
    #[AsCallback(table: 'tl_form', target: 'config.onload')]
    #[AsCallback(table: 'tl_form_field', target: 'config.onload')]
    public function addPalette(DataContainer $dc): void
    {
        $eventDispatcher = System::getContainer()->get('event_dispatcher');

        $pm = PaletteManipulator::create()
            ->addLegend('style_manager_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE)
            ->addField(['styleManager'], 'style_manager_legend', PaletteManipulator::POSITION_APPEND)
        ;

        foreach ($GLOBALS['TL_DCA'][ $dc->table ]['palettes'] as $palette => $value)
        {
            $event = new AddStyleManagerPaletteEvent($dc, $palette);
            $eventDispatcher->dispatch($event);

            $palette = $event->getPalette();

            if ($palette === '__selector__' || $palette === '__skip__')
            {
                continue;
            }

            $pm->applyToPalette($palette, $dc->table);
        }
    }

    #[AsCallback(table: 'tl_form_field', target: 'list.sorting.child_record')]
    public function listFormFields(array $arrRow): string
    {
        $arrStyles = StringUtil::deserialize($arrRow['styleManager']);
        $arrRow['styleManager'] = new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);

        $formField = new \tl_form_field();
        return $formField->listFormFields($arrRow);
    }

    public static function resetClasses(mixed $varValue, DataContainer $dc, string $strTable): mixed
    {
        if (self::isMultipleField($dc->field))
        {
            $cssID = StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1] ?? '';
        }

        $objStyles = StyleManagerModel::findByTableAndConfiguration($strTable);
        $arrStyles = array();

        if ($objStyles !== null)
        {
            foreach ($objStyles as $objStyle)
            {
                $arrGroup = StringUtil::deserialize($objStyle->cssClasses, true);

                foreach ($arrGroup as $opts)
                {
                    $arrStyles[] = ' ' . $opts['key'] . ' ';
                }
            }

            $arrStyles = array_filter($arrStyles);
        }

        if (count($arrStyles))
        {
            $varValue  = ' ' . $varValue . ' ';

            $varValue = str_replace($arrStyles, ' ', $varValue);
            $varValue = trim((string) preg_replace('#\s+#', ' ', $varValue));
        }

        if (self::isMultipleField($dc->field))
        {
            $varValue = serialize(array(($cssID[0] ?? ''), $varValue));
        }

        return $varValue;
    }

    public static function updateClasses(mixed $varValue, DataContainer $dc): mixed
    {
        if (self::isMultipleField($dc->field))
        {
            $cssID = StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1] ?? '';
        }

        $varValues = StringUtil::deserialize($dc->activeRecord->styleManager, true);

        // remove vars node
        if (isset($varValues[StyleManager::VARS_KEY]))
        {
            unset($varValues[StyleManager::VARS_KEY]);
        }

        // append classes
        $varValue .= ($varValue ? ' ' : '') . (count($varValues) ? implode(' ', $varValues) : '');
        $varValue  = trim($varValue);

        if (self::isMultipleField($dc->field))
        {
            $varValue = serialize(array(($cssID[0] ?? ''), $varValue));
        }

        return $varValue;
    }

    private static function isMultipleField(string $strField): bool
    {
        return StyleManager::$validCssClassFields[ $strField ] > 1;
    }

    private function clearClasses(mixed $varValue, DataContainer $dc): mixed
    {
        if (self::isMultipleField($dc->field))
        {
            $cssID = StringUtil::deserialize($varValue, true);
            $varValue = $cssID[1] ?? '';
        }

        $arrValues = StringUtil::deserialize($dc->activeRecord->styleManager, true);
        $arrValues = StyleManager::deserializeValues($arrValues);

        // remove non-existing values
        self::cleanupClasses($arrValues, $dc->table);

        if (count($arrValues))
        {
            foreach ($arrValues as $k => $v)
            {
                $arrValues[ $k ] = ' ' . $v . ' ';
            }

            // Might need a strict type check for varValue in the future as all following operations won't work with id or null
            $varValue = ' ' . $varValue . ' ';
            $varValue = str_replace($arrValues, '  ', $varValue);
            $varValue = trim(preg_replace('#\s+#', ' ', $varValue));
        }

        if (self::isMultipleField($dc->field))
        {
            $varValue = serialize(array(($cssID[0] ?? ''), $varValue));
        }

        return $varValue;
    }

    private static function cleanupClasses(mixed &$arrValues, string $strTable): void
    {
        if (is_array($arrValues))
        {
            $objStyles = StyleManagerModel::findByTableAndConfiguration($strTable);

            if ($objStyles !== null)
            {
                $arrExistingKeys = array();
                $arrExistingValues = array();
                $arrArchives = array();

                $objStyleArchives = StyleManagerArchiveModel::findAllWithConfiguration();

                // Prepare archives identifier
                foreach ($objStyleArchives as $objStyleArchive)
                {
                    $arrArchives[ $objStyleArchive->id ?? $objStyleArchive->identifier ] =  $objStyleArchive->identifier;
                }

                foreach ($objStyles as $objStyle)
                {
                    $arrExistingKeys[] = StyleManager::generateAlias($arrArchives[ $objStyle->pid ] ?? '', $objStyle->alias);

                    $arrGroup = StringUtil::deserialize($objStyle->cssClasses, true);

                    foreach ($arrGroup as $opts)
                    {
                        $arrExistingValues[] = $opts['key'];
                    }
                }

                foreach ($arrValues as $key => $value)
                {
                    if (!in_array($key, $arrExistingKeys))
                    {
                        unset($arrValues[$key]);
                        continue;
                    }

                    if (!in_array($value, $arrExistingValues))
                    {
                        unset($arrValues[$key]);
                    }
                }
            }
            else
            {
                $arrValues = array();
            }
        }
    }
}
