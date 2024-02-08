<?php

namespace App\Repository\Power;

use App\Entity\Power\Hourly;
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

    /**
     * Returns the current power consumption in watt
     */
    public function getLatestValue()
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT ROUND(value * POWER(10, scaler), 2) AS consumption FROM power_hourly ORDER BY ts DESC LIMIT 1';
        $resultSet = $conn->executeQuery($sql);

        $result = $resultSet->fetchFirstColumn();

        return $result[0] ?? 0;
    }

    public function getLastHours(int $hours = 48)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT UNIX_TIMESTAMP(ts) as timestamp , ROUND(value * POWER(10, scaler), 2) AS consumption FROM power_hourly WHERE ts > DATE_SUB(NOW(), INTERVAL :hours HOUR) ORDER BY ts ASC';
        $resultSet = $conn->executeQuery($sql, ['hours' => $hours]);

        return $resultSet->fetchAllAssociative();
    }
}
