<?php

namespace App\Repository\Gas;

use App\Entity\Gas\Hourly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hourly>
 *
 * @method Hourly|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hourly|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hourly[]    findAll()
 * @method Hourly[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HourlyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hourly::class);
    }

    public function getLastHours(int $hours = 48)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
WITH RECURSIVE all_dates(dt) AS (
    -- anchor
    SELECT DATE_SUB(now(), INTERVAL :hours HOUR) dt
    UNION ALL 
    -- recursion with stop condition
    SELECT dt + interval 1 HOUR FROM all_dates WHERE dt + interval 1 HOUR <= now()
)
SELECT UNIX_TIMESTAMP(ad.dt) as timestamp, IFNULL(consumption,0) as consumption
FROM all_dates as ad
LEFT JOIN (
    SELECT DATE_FORMAT(ts, '%Y-%m-%d %H:00') AS myday, ROUND(value * 0.01) AS consumption 
    FROM gas_hourly
    WHERE ts >= DATE_SUB(now(), INTERVAL 24 HOUR) 
    ORDER BY ts ASC
) gd
ON DATE_FORMAT(ad.dt, '%Y-%m-%d %H:00') = gd.myday
ORDER BY ad.dt ASC
SQL;

        $resultSet = $conn->executeQuery($sql, ['hours' => $hours]);

        return $resultSet->fetchAllAssociative();
    }
}
