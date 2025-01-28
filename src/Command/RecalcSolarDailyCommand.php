<?php

namespace App\Command;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:recalc-solar-daily',
    description: 'Add a short description for your command',
)]
class RecalcSolarDailyCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $conn = $this->entityManager->getConnection();
        $sql = 'SELECT ts, value, total FROM solar_daily ORDER BY ts ASC';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery();
        $solarDailies = $result->fetchAllAssociative();

        $total = 0;

        $stmt = $conn->prepare('UPDATE solar_daily SET total = :total WHERE ts = :ts');

        foreach ($solarDailies as $daily) {
            $total += $daily['value'];

            $stmt->bindValue('total', $total);
            $stmt->bindValue('ts', $daily['ts']);

            $stmt->executeStatement();
        }

        return Command::SUCCESS;
    }
}
