<?php

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
        $arrStyles = StringUtil::deserialize($data['styleManager'], true);

        return new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);
    }
}
