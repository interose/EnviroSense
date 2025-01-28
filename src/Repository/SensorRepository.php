<?php

namespace App\Repository;

use App\Entity\Sensor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @throws Exception
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

    public function getLatestValues()
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
}
