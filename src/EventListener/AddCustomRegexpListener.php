<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Widget;

#[AsHook('addCustomRegexp')]
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
