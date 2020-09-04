<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

use Contao\System;

class Support
{
    /**
     * Support Rocksolid Custom Elements
     *
     * @param DataContainer $dc
     *
     * @deprecated No longer needed from version 2.4.0
     */
    public function extendRockSolidCustomElementsPalettes($dc)
    {
        $packages = System::getContainer()->getParameter('kernel.packages');
        $version  = floatval($packages['oveleon/contao-component-style-manager']);

        if($version >= 2.4 || $version == 0){
            return;
        }

        foreach ($GLOBALS['TL_DCA'][$dc->table]['palettes'] as $key => &$palette)
        {
            if (!is_array($palette) && strpos($key, 'rsce_') === 0)
            {
                preg_match_all('/(.*)({expert_legend.*)/', $palette, $matches);

                $palette = $matches[1][0] . '{style_manager_legend},styleManager;' . $matches[2][0];
            }
        }
    }
}
