<?php

namespace App\Repository;

use App\Entity\HeatingSystem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HeatingSystem>
 *
 * @method HeatingSystem|null find($id, $lockMode = null, $lockVersion = null)
 * @method HeatingSystem|null findOneBy(array $criteria, array $orderBy = null)
 * @method HeatingSystem[]    findAll()
 * @method HeatingSystem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HeatingSystemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HeatingSystem::class);
    }

    public function getLatestValueByProperty(string $property)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT value FROM heating_system WHERE name = :property ORDER BY ts DESC LIMIT 1';
        $resultSet = $conn->executeQuery($sql, ['property' => $property]);

        $result = $resultSet->fetchFirstColumn();

        return $result[0] ?? 0;
    }

//    /**
//     * @return HeatingSystem[] Returns an array of HeatingSystem objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HeatingSystem
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
