<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Event;

use Contao\DataContainer;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\Style\StyleGroup;
use Symfony\Contracts\EventDispatcher\Event;

class IsVisibleGroupEvent extends Event
{
    private bool $visible = false;

    public function __construct(
        public readonly StyleGroup|StyleManagerModel $group,
        public readonly string $table,
    ) {
    }

    public function getGroup(): StyleGroup|StyleManagerModel
    {
        return $this->group;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function setVisible(): void
    {
        $this->visible = true;
    }
}
