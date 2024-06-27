<?php

namespace App\Repository;

use App\Entity\SensorDescription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SensorDescription>
 *
 * @method SensorDescription|null find($id, $lockMode = null, $lockVersion = null)
 * @method SensorDescription|null findOneBy(array $criteria, array $orderBy = null)
 * @method SensorDescription[]    findAll()
 * @method SensorDescription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SensorDescriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SensorDescription::class);
    }
}
