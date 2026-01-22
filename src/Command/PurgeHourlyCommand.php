<?php

namespace App\Command;

use App\Repository\HourlyDataRepositoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:purge-hourly',
    description: 'Purges data older than specified days from hourly tables',
)]
class PurgeHourlyCommand extends Command
{
    public function __construct(
        private iterable $hourlyRepositories
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('days', 'd', InputOption::VALUE_OPTIONAL, 'Number of days to keep', 90)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show what would be deleted without actually deleting')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $days = (int) $input->getOption('days');
        $dryRun = $input->getOption('dry-run');

        $io->title('Data Purge Command');
        $io->info(sprintf('Purging data older than %d days', $days));

        if ($dryRun) {
            $io->warning('DRY RUN MODE - No data will be deleted');
        }

        $totalDeleted = 0;
        $processedTables = 0;

        foreach ($this->hourlyRepositories as $repository) {
            if (!$repository instanceof HourlyDataRepositoryInterface) {
                continue;
            }

            $tableName = $repository->getTableName();
            $io->section("Processing table: {$tableName}");

            try {
                if ($dryRun) {
                    $count = $repository->countOldRecords($days);
                    $io->text(sprintf('Would delete %d records', $count));
                    $totalDeleted += $count;
                } else {
                    $deleted = $repository->purgeOldData($days);
                    $io->success(sprintf('Deleted %d records', $deleted));
                    $totalDeleted += $deleted;
                }
                $processedTables++;
            } catch (\Exception $e) {
                $io->error(sprintf('Error processing %s: %s', $tableName, $e->getMessage()));
            }
        }

        $io->newLine();
        $io->success(sprintf(
            'Completed! %s %d records from %d tables',
            $dryRun ? 'Would delete' : 'Deleted',
            $totalDeleted,
            $processedTables
        ));

        return Command::SUCCESS;
    }
}
