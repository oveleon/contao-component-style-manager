<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use Contao\Widget;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;

/**
 * @Hook("loadFormField")
 */
class LoadFormFieldListener
{
    /**
     * Parse Template and set Variables
     */
    public function __invoke(Widget $objWidget)
    {
        if(!($objWidget->styleManager instanceof Styles))
        {
            $arrStyles = StringUtil::deserialize($objWidget->styleManager);
            $objWidget->styleManager = new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);
        }

        return $objWidget;
    }
}
