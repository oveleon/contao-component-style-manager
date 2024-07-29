<?php

declare(strict_types=1);

/*
 * This file is part of ContaoComponentStyleManager.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoComponentStyleManager\Command;

use Oveleon\ContaoComponentStyleManager\StyleManager\Sync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Converts the StyleManager object to the new schema.
 *
 * @internal
 */
class ObjectConversionCommand extends Command
{
    protected static $defaultName = 'contao:stylemanager:object-conversion';

    public function __construct(private readonly Sync $sync)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Converts the StyleManager object to the new schema based on the given table.')
            ->addArgument('table', InputArgument::REQUIRED, 'The name of the Table')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Check tables even if they have already been converted.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $strTable = $input->getArgument('table');
        $blnForce = $input->getOption('force');

        if(null === $strTable)
        {
            throw new InvalidArgumentException('Please enter an existing table');
        }

        if($this->sync->shouldRunObjectConversion($strTable) || $blnForce)
        {
            $io->writeln(sprintf('Start converting table %s', $strTable));

            $this->sync->performObjectConversion($strTable);

            $io->success(sprintf('Table %s were successfully converted.', $strTable));
        }
        else
        {
            $io->note(sprintf('The table %s seems to be already converted. Use the --force option to convert this table anyway.', $strTable));
        }

        return Command::SUCCESS;
    }
}
