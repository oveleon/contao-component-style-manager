<?php

declare(strict_types=1);

namespace Oveleon\ContaoComponentStyleManager\StyleManager;

enum ConfigurationFileType: string
{
    case XML = '.xml';
    case YAML = '.yaml';
}
