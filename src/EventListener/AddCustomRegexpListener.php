<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Widget;

/**
 * @Hook("addCustomRegexp")
 */
class AddCustomRegexpListener
{
    /**
     * Add a new regexp "variable"
     */
    public function __invoke($strRegexp, $varValue, Widget $objWidget): bool
    {
        if ($strRegexp == 'variable')
        {
            if (!preg_match('/^[a-zA-Z](?:_?[a-zA-Z0-9]+)$/', $varValue))
            {
                $objWidget->addError('Field ' . $objWidget->label . ' must begin with a letter and may not contain any spaces or special characters (e.g. myVariable).');
            }

            return true;
        }

        return false;
    }
}
