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
use Contao\Template;
use Oveleon\ContaoComponentStyleManager\StyleManager\StyleManager;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;

#[AsHook('parseTemplate')]
class ParseTemplateListener
{
    /**
     * Parse Template and set Variables
     */
    public function __invoke(Template $template): void
    {
        // Check page and template variables to pass them to the template
        if (str_starts_with($template->getName(), 'fe_page')) {
            global $objPage;

            $arrStyles = array_filter(array_merge_recursive(
                StringUtil::deserialize($objPage->styleManager, true),
                StringUtil::deserialize($template->layout->styleManager, true)
            ));

            $template->styleManager = serialize($arrStyles);
        }

        // Build Styles object and assign it to the template
        if (!($template->styleManager instanceof Styles))
        {
            $arrStyles = StringUtil::deserialize($template->styleManager);
            $template->styleManager = new Styles($arrStyles[StyleManager::VARS_KEY] ?? null);
        }
    }
}
