<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;

#[AsHook('loadFormField')]
class LoadFormFieldListener
{
    /**
     * Parse Template and set Variables
     */
    public function __invoke(Widget $objWidget): Widget
    {
        if (!($objWidget->styleManager instanceof Styles))
        {
            $arrStyles = StringUtil::deserialize($objWidget->styleManager);
            $objWidget->styleManager = new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);
        }

        return $objWidget;
    }
}
