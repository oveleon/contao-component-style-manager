<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Database;
use Contao\DataContainer;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerArchiveModel;

/**
 * @internal
 */
readonly class StyleManagerArchiveListener
{
    #[AsCallback(table: 'tl_style_manager_archive', target: 'config.onload')]
    public function checkIdentifier($dc): void
    {
        $objArchive = StyleManagerArchiveModel::findById($dc->id);

        if (null !== $objArchive && $objArchive->identifier)
        {
            $GLOBALS['TL_DCA']['tl_style_manager_archive']['fields']['identifier']['eval']['mandatory'] = false;
            $GLOBALS['TL_DCA']['tl_style_manager_archive']['fields']['identifier']['eval']['disabled'] = true;
        }
    }

    #[AsCallback(table: 'tl_style_manager_archive', target: 'list.label.label')]
    public function addIdentifierInfo($row, $label)
    {
        if ($row['identifier'])
        {
            $label .= sprintf('<span style="color:var(--gray);padding-left:3px">[%s]</span>', $row['identifier']);
        }

        return $label;
    }

    /**
     * @throws \Exception
     */
    #[AsCallback(table: 'tl_style_manager_archive', target: 'fields.identifier.save')]
    public function generateIdentifier($varValue, DataContainer $dc): string
    {
        $aliasExists = function (string $alias) use ($dc): bool
        {
            $objDatabase = Database::getInstance();

            return $objDatabase->prepare("SELECT id FROM tl_style_manager_archive WHERE identifier=? AND id!=?")->execute($alias, $dc->id)->numRows > 0;
        };

        if ($aliasExists($varValue))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['identifierExists'], $varValue));
        }

        return $varValue;
    }
}
