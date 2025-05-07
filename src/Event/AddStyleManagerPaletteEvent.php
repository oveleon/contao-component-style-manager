<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Event;

use Contao\DataContainer;
use Symfony\Contracts\EventDispatcher\Event;

class AddStyleManagerPaletteEvent extends Event
{
    public function __construct(
        public readonly DataContainer $dc,
        public string $palette,
    ) {
    }

    public function getPalette(): string
    {
        return $this->palette;
    }

    public function skipPalette(): void
    {
        $this->palette = '__skip__';
    }
}
