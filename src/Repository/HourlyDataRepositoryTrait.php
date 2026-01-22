<?php

namespace App\Repository;

use Doctrine\DBAL\Connection;

trait HourlyDataRepositoryTrait
{
    public function purgeOldData(int $days): int
    {
        $cutoffDate = new \DateTime();
        $cutoffDate->modify("-{$days} days");

        return $this->createQueryBuilder('e')
            ->delete()
            ->where('e.ts < :cutoffDate')
            ->setParameter('cutoffDate', $cutoffDate)
            ->getQuery()
            ->execute();
    }

    public function countOldRecords(int $days): int
    {
        $cutoffDate = new \DateTime();
        $cutoffDate->modify("-{$days} days");

        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.ts)')
            ->where('e.ts < :cutoffDate')
            ->setParameter('cutoffDate', $cutoffDate)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getTableName(): string
    {
        return $this->getClassMetadata()->getTableName();
    }
}