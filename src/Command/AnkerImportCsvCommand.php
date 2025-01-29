<?php

namespace App\Command;

use App\Lib\AnkerDailyDto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Entity\Anker\Daily as AnkerDaily;

#[AsCommand(
    name: 'app:anker-import-csv',
    description: 'Imports the anker csv file to the database',
)]
class AnkerImportCsvCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('csv', InputArgument::REQUIRED, 'The anker csv file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvFile = $input->getArgument('csv');

        if (basename($csvFile) === $csvFile) {
            $currentPath = getcwd();
            $fullPath = $currentPath . DIRECTORY_SEPARATOR . $csvFile;
        } else {
            $fullPath = $csvFile;
        }

        if (!file_exists($fullPath)) {
            $io->error('Could not find the csv file');

            return Command::FAILURE;
        }

        $repository = $this->entityManager->getRepository(AnkerDaily::class);

        $progressBar = new ProgressBar($output);
        $handle = fopen($fullPath, 'r');
        while (($line = fgetcsv($handle)) !== false) {
            $date = \DateTime::createFromFormat('Y-m-d', $line[0]);
            if ($date === false) {
                continue;
            }

            $obj = AnkerDailyDto::fromCsv($line);
            $repository->update($obj);

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->success('Finished!');

        return Command::SUCCESS;
    }
}
