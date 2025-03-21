<?php

namespace App\Repository\Photovoltaics;

use App\Entity\Photovoltaics\Daily;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Daily>
 *
 * @method Daily|null find($id, $lockMode = null, $lockVersion = null)
 * @method Daily|null findOneBy(array $criteria, array $orderBy = null)
 * @method Daily[]    findAll()
 * @method Daily[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
    public function update(int $total): void
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
INSERT INTO photovoltaics_daily (ts, total)
VALUES (NOW(), :total) ON DUPLICATE KEY UPDATE total = :total
SQL;
        $conn->executeStatement($sql, [
            'total' => $total,
        ]);
    }

    /**
     * @throws Exception
     */
    public function updateTodaysYield(): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
UPDATE photovoltaics_daily as dest,
(
	SELECT total
	FROM photovoltaics_daily
	WHERE ts = DATE_FORMAT(NOW(), '%Y-%c-%d')
) as today,
(
	SELECT total
	FROM photovoltaics_daily
	WHERE ts = DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%c-%d')
) as yesterday
SET dest.value = (today.total - yesterday.total)
WHERE 
ts = DATE_FORMAT(NOW(), '%Y-%c-%d');
SQL;
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement();
    }

    /**
     * Returns the todays pv yield.
     * @throws Exception
     */
    public function getTodaysYield()
    {
        $today = new \DateTime();

        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT value AS yield FROM photovoltaics_daily WHERE ts = :today LIMIT 1';
        $resultSet = $conn->executeQuery($sql, ['today' => $today->format('Y-m-d')]);

        $result = $resultSet->fetchFirstColumn();

        return $result[0] ?? 0;
    }

    /**
     * Returns the pv yield of the last days in kWh.
     * @throws Exception
     */
    public function getLastDays(int $lastDays = 7): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT DATE_FORMAT(ts, \'%a\') AS weekday, ROUND(value / 1000, 1) AS yield FROM photovoltaics_daily WHERE ts >= DATE_SUB(now(), INTERVAL :lastDays DAY) ORDER BY ts ASC';
        $resultSet = $conn->executeQuery($sql, ['lastDays' => $lastDays]);

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Return the pv yield grouped by year in kWh.
     * @throws Exception
     */
    public function getGroupedByYear(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT YEAR(ts) AS year, ROUND(SUM(value) / 1000) AS yield FROM photovoltaics_daily GROUP BY YEAR(ts) ORDER BY YEAR(ts) ASC';
        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Return the pv yield grouped by month in kWh.
     * @throws Exception
     */
    public function getLastMonthsByMonths(int $lastMonths = 7): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
WITH RECURSIVE all_dates(dt) AS (
    -- anchor
    SELECT DATE_SUB(now(), INTERVAL :lastMonths MONTH) dt
    UNION ALL 
    -- recursion with stop condition
    SELECT dt + interval 1 MONTH FROM all_dates WHERE dt + interval 1 MONTH <= now()
)
SELECT DATE_FORMAT(ad.dt, '%Y-%m') as ym, GROUP_CONCAT(DISTINCT DATE_FORMAT(ad.dt, '%b')) as monthname, IFNULL(sum(yield),0) as yield
FROM all_dates as ad
LEFT JOIN (
	SELECT DATE_FORMAT(ts, '%Y-%m') AS my_month , ROUND(SUM(value) / 1000) AS yield
	FROM photovoltaics_daily
	GROUP BY DATE_FORMAT(ts, '%Y-%m')
	ORDER BY DATE_FORMAT(ts, '%Y-%m') ASC
) gd
ON DATE_FORMAT(ad.dt, '%Y-%m') = gd.my_month
GROUP BY ym
ORDER BY ym ASC;
SQL;
        $resultSet = $conn->executeQuery($sql, [
            'lastMonths' => $lastMonths,
        ]);

        return $resultSet->fetchAllAssociative();
    }

    /**
     * Return the solar yield grouped by month in kWh.
     * @throws Exception
     */
    public function getLastMonthsByMonthsYearBefore(int $lastMonths = 7): array
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = <<<SQL
WITH RECURSIVE all_dates(dt) AS (
    -- anchor
    SELECT DATE_SUB(DATE_SUB(now(), INTERVAL 1 YEAR), INTERVAL :lastMonths MONTH) dt
    UNION ALL 
    -- recursion with stop condition
    SELECT dt + interval 1 MONTH FROM all_dates WHERE dt + interval 1 MONTH <= DATE_SUB(now(), INTERVAL 1 YEAR)
)
SELECT DATE_FORMAT(ad.dt, '%Y-%m') as ym, GROUP_CONCAT(DISTINCT DATE_FORMAT(ad.dt, '%b')) as monthname, IFNULL(sum(yield),0) as yield
FROM all_dates as ad
LEFT JOIN (
	SELECT DATE_FORMAT(ts, '%Y-%m') AS my_month, ROUND(SUM(value) / 1000) AS yield
	FROM photovoltaics_daily
	GROUP BY DATE_FORMAT(ts, '%Y-%m')
	ORDER BY DATE_FORMAT(ts, '%Y-%m') ASC
) gd
ON DATE_FORMAT(ad.dt, '%Y-%m') = gd.my_month
GROUP BY ym
ORDER BY ym ASC;
SQL;
        $resultSet = $conn->executeQuery($sql, [
            'lastMonths' => $lastMonths,
        ]);

        return $resultSet->fetchAllAssociative();
    }
}
