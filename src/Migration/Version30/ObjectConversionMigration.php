<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Migration\Version30;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Oveleon\ContaoComponentStyleManager\StyleManager\Sync;

class ObjectConversionMigration extends AbstractMigration
{
    public function __construct(
        private readonly Connection $connection,
        private readonly Sync $sync,
    ) {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        // If the database table itself does not exist we should do nothing
        if (!$schemaManager->tablesExist(['tl_style_manager']))
        {
            return false;
        }

        return $this->sync->shouldRunObjectConversion('tl_article')
            || $this->sync->shouldRunObjectConversion('tl_content')
            || $this->sync->shouldRunObjectConversion('tl_calendar_events')
            || $this->sync->shouldRunObjectConversion('tl_form')
            || $this->sync->shouldRunObjectConversion('tl_form_field')
            || $this->sync->shouldRunObjectConversion('tl_layout')
            || $this->sync->shouldRunObjectConversion('tl_module')
            || $this->sync->shouldRunObjectConversion('tl_news')
            || $this->sync->shouldRunObjectConversion('tl_page')
        ;
    }

    public function run(): MigrationResult
    {
        if($this->sync->shouldRunObjectConversion('tl_article'))
            $this->sync->performObjectConversion('tl_article');

        if($this->sync->shouldRunObjectConversion('tl_content'))
            $this->sync->performObjectConversion('tl_content');

        if($this->sync->shouldRunObjectConversion('tl_calendar_events'))
            $this->sync->performObjectConversion('tl_calendar_events');

        if($this->sync->shouldRunObjectConversion('tl_form'))
            $this->sync->performObjectConversion('tl_form');

        if($this->sync->shouldRunObjectConversion('tl_form_field'))
            $this->sync->performObjectConversion('tl_form_field');

        if($this->sync->shouldRunObjectConversion('tl_layout'))
            $this->sync->performObjectConversion('tl_layout');

        if($this->sync->shouldRunObjectConversion('tl_module'))
            $this->sync->performObjectConversion('tl_module');

        if($this->sync->shouldRunObjectConversion('tl_news'))
            $this->sync->performObjectConversion('tl_news');

        if($this->sync->shouldRunObjectConversion('tl_page'))
            $this->sync->performObjectConversion('tl_page');

        return new MigrationResult(
            true,
            'StyleManager configurations were successfully converted. Please note, if custom tables have been added to the StyleManager, they must be migrated using the `contao:stylemanager:object-conversion table` command.'
        );
    }
}
