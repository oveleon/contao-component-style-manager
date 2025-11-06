<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Event;

use Contao\Model\Collection;
use Oveleon\ContaoComponentStyleManager\Model\StyleManagerModel;
use Symfony\Contracts\EventDispatcher\Event;

class StyleManagerFindByTableEvent extends Event
{
    private Collection|StyleManagerModel|array|null $collection = null;

    public function __construct(
        public readonly string $table,
        public readonly array $options,
    ) {
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setCollection(Collection|StyleManagerModel|array|null $collection): void
    {
        $this->collection = $collection;
    }

    public function getCollection(): Collection|StyleManagerModel|array|null
    {
        return $this->collection;
    }
}
