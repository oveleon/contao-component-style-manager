<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Twig;

use Contao\StringUtil;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class StyleManagerExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('styleManager', [$this, 'createContext']),
        ];
    }

    public function createContext(array $data): Styles
    {
        // ToDo: Enable when contao ships fe_page as a twig template
        /*if($GLOBALS['TL_PTY'][$data['type']] ?? false)
        {
            global $objPage;

            $layout = LayoutModel::findByPk($data['layoutId']);

            $arrStyles = array_filter(array_merge_recursive(
                StringUtil::deserialize($objPage?->styleManager, true),
                StringUtil::deserialize($layout?->styleManager, true)
            ));
        }
        else
        {
            $arrStyles = StringUtil::deserialize($data['styleManager'], true);
        }*/

        $arrStyles = StringUtil::deserialize($data['styleManager'], true);

        return new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);
    }
}
