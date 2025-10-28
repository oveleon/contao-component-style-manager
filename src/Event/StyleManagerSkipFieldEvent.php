<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Event;

use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Oveleon\ContaoComponentStyleManager\Style\StyleGroup;
use Oveleon\ContaoComponentStyleManager\Widget\ComponentStyleSelect;
use Symfony\Contracts\EventDispatcher\Event;

class StyleManagerSkipFieldEvent extends Event
{
    private bool $skip = false;

    public function __construct(
        public readonly StyleGroup|StyleManagerModel $group,
        public readonly ComponentStyleSelect $widget,
    ) {
    }

    public function getGroup(): StyleGroup|StyleManagerModel
    {
        return $this->group;
    }

    public function getWidget(): ComponentStyleSelect
    {
        return $this->widget;
    }

    public function shouldSkip(): bool
    {
        return $this->skip;
    }

    public function skip(): void
    {
        $this->skip = true;
    }
}
