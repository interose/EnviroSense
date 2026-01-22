<?php

namespace App\Repository;

interface HourlyDataRepositoryInterface
{
    /**
     * Purge data older than specified number of days
     *
     * @param int $days Number of days to keep
     * @return int Number of deleted records
     */
    public function purgeOldData(int $days): int;

    /**
     * Count records that would be deleted
     *
     * @param int $days Number of days to keep
     * @return int Number of records that would be deleted
     */
    public function countOldRecords(int $days): int;

    /**
     * Get the table name for logging purposes
     *
     * @return string
     */
    public function getTableName(): string;
}