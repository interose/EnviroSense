<?php

namespace App\Repository;

use App\Entity\Sensor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sensor>
 *
 * @method Sensor|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sensor|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sensor[]    findAll()
 * @method Sensor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SensorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sensor::class);
    }

    /**
     * @throws/Exception
     */
    public function update(string $mac, array $payload): void
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'INSERT INTO sensor (ts, mac, payload) VALUES (NOW(), :mac, :payload)';
        $conn->executeStatement($sql, [
            'mac' => $mac,
            'payload' => json_encode($payload),
        ]);
    }

    /**
     * @throws Exception
     */
    public function getLatestValues(): array
    {
        $conn = $this->getEntityManager()->getConnection();

        $sql = <<<SQL
SELECT s.mac, s.payload, s.ts, sd.name, sd.color
FROM sensor s
INNER JOIN (
    SELECT MAX(id) as id, mac FROM sensor GROUP BY mac
) AS src ON src.id = s.id
LEFT JOIN sensor_description as sd ON s.mac = sd.mac
WHERE sd.name IS NOT NULL 
ORDER BY sd.sequence
SQL;

        $resultSet = $conn->executeQuery($sql);

        return $resultSet->fetchAllAssociative();
    }

    public function getLastHours(int $pastHours = 48)
    {
        $now = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $now->modify(sprintf('- %d hours', $pastHours));

        $query = $this->createQueryBuilder('p')
            ->andWhere('p.ts > :val')
            ->setParameter('val', $now->format('Y-m-d H:i:s'))
            ->orderBy('p.ts', 'ASC')
            ->getQuery()
        ;

        return $query->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getLatestDewpointValues()
    {
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.mac = :mac')
            ->setParameter('mac', Sensor::DEWPOINT_SENSOR_MAC)
            ->orderBy('s.ts', 'DESC')
            ->setMaxResults(1)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function getLastHoursDewpointValues(int $pastHours = 48)
    {
        $now = new \DateTime('now', new \DateTimeZone('Europe/Berlin'));
        $now->modify(sprintf('- %d hours', $pastHours));

        $query = $this->createQueryBuilder('p')
            ->andWhere('p.ts > :val')
            ->setParameter('val', $now->format('Y-m-d H:i:s'))
            ->andWhere('p.mac = :mac')
            ->setParameter('mac', Sensor::DEWPOINT_SENSOR_MAC)
            ->orderBy('p.ts', 'ASC')
            ->getQuery()
        ;

        return $query->getResult(AbstractQuery::HYDRATE_ARRAY);

    }
}
