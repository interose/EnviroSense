<?php

namespace App\Repository\Anker;

use App\Entity\Anker\Hourly;
use App\Lib\AnkerHourlyDto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hourly>
 */
class HourlyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hourly::class);
    }

    public function add(AnkerHourlyDto $obj)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
INSERT INTO anker_hourly (ts, power_unit, solar_power1, solar_power2, solar_power3, solar_power4, battery_soc, battery_energy, charging_power)
VALUES (NOW(), :power_unit, :solar_power1, :solar_power2, :solar_power3, :solar_power4, :battery_soc, :battery_energy, :charging_power)
SQL;
        $conn->executeStatement($sql, [
            'power_unit' => $obj->powerUnit,
            'solar_power1' => $obj->solarPower1,
            'solar_power2' => $obj->solarPower2,
            'solar_power3' => $obj->solarPower3,
            'solar_power4' => $obj->solarPower4,
            'battery_soc' => $obj->batterySoc,
            'battery_energy' => $obj->batteryEnergy,
            'charging_power' => $obj->chargingPower,
        ]);
    }
}
