<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Twig;

use Contao\StringUtil;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;
use Oveleon\ContaoComponentStyleManager\Util\StyleManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @internal
 */
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
        /** @var array $arrStyles */
        $arrStyles = StringUtil::deserialize($data['styleManager'], true);

        return new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);
    }
}
