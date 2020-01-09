<?php
/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager;

class Styles
{
    /**
     * Style Collection
     * @var array|null
     */
    private $styles = null;

    /**
     * Initialize the object
     *
     * @param array $arrStyles
     */
    public function __construct($arrStyles=null)
    {
        $this->styles = $arrStyles;
    }

    /**
     * Return the css class collection of an identifier
     *
     * @param $identifier
     * @param null $arrGroups
     *
     * @return string
     */
    public function get($identifier, $arrGroups=null)
    {
        if($this->styles === null || !is_array($this->styles[ $identifier ]))
        {
            return '';
        }

        // return full collection
        if($arrGroups === null)
        {
            return implode(" ", $this->styles[ $identifier ]);
        }

        // return parts of archive (groups)
        if(is_array($arrGroups))
        {
            $collection = array();

            foreach ($arrGroups as $groupAlias)
            {
                if($value = $this->styles[ $identifier ][ $groupAlias ])
                {
                    $collection[] = $value;
                }
            }

            return  implode(" ", $collection);
        }

        return '';
    }
}
