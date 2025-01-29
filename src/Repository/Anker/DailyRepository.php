<?php

namespace App\Repository\Anker;

use App\Entity\Anker\Daily;
use App\Lib\AnkerDailyDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Daily>
 */
class DailyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Daily::class);
    }

    /**
     * @throws Exception
     */
    public function update(AnkerDailyDto $obj): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
INSERT INTO anker_daily (date, battery_discharge, home_usage, grid_to_home, solar_production, battery_charge, solar_to_grid, battery_percentage, solar_percentage)
VALUES (:date, :battery_discharge, :home_usage, :grid_to_home, :solar_production, :battery_charge, :solar_to_grid, :battery_percentage, :solar_percentage)
ON DUPLICATE KEY UPDATE battery_discharge = :battery_discharge, home_usage = :home_usage, grid_to_home = :grid_to_home, solar_production = :solar_production, battery_charge = :battery_charge, solar_to_grid = :solar_to_grid, battery_percentage = :battery_percentage, solar_percentage = :solar_percentage
SQL;
        $conn->executeStatement($sql, [
            'date' => $obj->ts->format('Y-m-d'),
            'battery_discharge' => $obj->batteryDischarge,
            'home_usage' => $obj->homeUsage,
            'grid_to_home' => $obj->gridToHome,
            'solar_production' => $obj->solarProduction,
            'battery_charge' => $obj->batteryCharge,
            'solar_to_grid' => $obj->solarToGrid,
            'battery_percentage' => $obj->batteryPercentage,
            'solar_percentage' => $obj->solarPercentage,
        ]);
    }
}
