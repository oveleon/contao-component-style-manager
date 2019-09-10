<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

class StyleManager
{
    /**
     * Add CSS-Class (fe_page)
     *
     * @param $objPage
     * @param $objLayout
     * @param $objPageRegular
     */
    public function generatePage($objPage, $objLayout, $objPageRegular)
    {
        $objPage->cssClass = $objPage->cssClass ? $objPage->cssClass . ' ' . $objPage->styleManagerCompiled : $objPage->styleManagerCompiled;
    }

    /**
     * Add CSS-Class (mod_article)
     *
     * @param $objRow
     */
    public function getArticle($objRow)
    {
        $arrCSS = \StringUtil::deserialize($objRow->cssID, true);
        $arrCSS[1] = trim($arrCSS[1] . ' ' . $objRow->styleManagerCompiled);
        $objRow->cssID = serialize($arrCSS);
    }

    /**
     * Add CSS-Class (ce_)
     *
     * @param $objTemplate
     */
    public function parseTemplate($objTemplate)
    {
        if(isset($objTemplate->typePrefix) && $objTemplate->typePrefix === 'ce_' && $objTemplate->styleManagerCompiled)
        {
            $objTemplate->class .= ' ' . $objTemplate->styleManagerCompiled;
        }
    }
}