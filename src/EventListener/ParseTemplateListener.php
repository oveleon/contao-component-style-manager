<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use Contao\Template;
use Oveleon\ContaoComponentStyleManager\StyleManager\Styles;

/**
 * @Hook("parseTemplate")
 */
class ParseTemplateListener
{
    /**
     * Parse Template and set Variables
     */
    public function __invoke(Template $template)
    {
        // Check page and template variables to pass them to the template
        if(strpos($template->getName(), 'fe_page') === 0)
        {
            global $objPage;

            $arrStyles = array_filter(array_merge_recursive(
                StringUtil::deserialize($objPage->styleManager, true),
                StringUtil::deserialize($template->layout->styleManager, true)
            ));

            $template->styleManager = serialize($arrStyles);
        }

        // Build Styles object and assign it to the template
        if(!($template->styleManager instanceof Styles))
        {
            $arrStyles = StringUtil::deserialize($template->styleManager);
            $template->styleManager = new Styles(isset($arrStyles['__vars__']) ? $arrStyles['__vars__'] : null);
        }
    }
}
