<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

class Support
{
    /**
     * Clear StyleManager classes from cssClass field
     *
     * @param DataContainer $dc
     */
    public function extendRockSolidCustomElementsPalettes($dc)
    {
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