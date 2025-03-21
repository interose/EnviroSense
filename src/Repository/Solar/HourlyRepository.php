<?php

namespace App\Repository\Solar;

use App\Entity\Solar\Hourly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception;

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

    /**
     * @throws Exception
     */
    public function update(int $todayTotal): void
    {
        $conn = $this->getEntityManager()->getConnection();

        // The SUM() and AVG() functions return a DECIMAL value. Doctrine make a string out of it.
        $sql = <<<SQL
SELECT SUM(value) as value 
FROM solar_hourly 
WHERE ts BETWEEN 
    DATE_FORMAT(NOW(), '%Y-%c-%d 00:00:00') AND 
    DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 HOUR), '%Y-%c-%d %H:59:59')
SQL;
        $resultSet = $conn->executeQuery($sql, ['todayTotal' => $todayTotal]);

        $prevTotal = $resultSet->fetchOne();
        if (false === $prevTotal) {
            // no previous value, so insert the current value
            $diff = $todayTotal;
        } else {
            // else, try to subtract the previous value from the current value
            $diff = max(0, $todayTotal - intval($prevTotal));
        }

        $sql = <<<SQL
INSERT INTO solar_hourly (ts, value) 
VALUES (DATE_FORMAT(NOW(), '%Y-%c-%d %H:00:00'), :value) ON DUPLICATE KEY UPDATE value = :value
SQL;
        $conn->executeStatement($sql, [
            'value' => $diff,
        ]);
    }

    /**
     * Returns the current solar yield in ?
     */
    public function getLatestValue()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT value AS yield FROM solar_hourly ORDER BY ts DESC LIMIT 1';
        $resultSet = $conn->executeQuery($sql);

        $result = $resultSet->fetchFirstColumn();

        return $result[0] ?? 0;
    }

    /**
     * @throws Exception
     */
    public function getLastHours(int $hours = 48): array
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
SELECT UNIX_TIMESTAMP(ad.dt) as timestamp, IFNULL(yield,0) as yield
FROM all_dates as ad
LEFT JOIN (
    SELECT DATE_FORMAT(ts, '%Y-%m-%d %H:00') AS myday, value AS yield 
    FROM solar_hourly
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
