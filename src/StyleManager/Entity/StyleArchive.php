<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\StyleManager\Entity;

final class StyleArchive
{
    private array $fields;

    public function __construct(public readonly int|string $identifier)
    {}

    public function __get(string $name): mixed
    {
        return $this->fields[$name] ?? null;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->fields[$name] = $value;
    }
}
