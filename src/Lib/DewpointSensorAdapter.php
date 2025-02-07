<?php

namespace App\Lib;

use App\Repository\SensorRepository;

class DewpointSensorAdapter
{
    public function __construct(
        private readonly SensorRepository $sensorRepository,
        public array $current = [],
        public array $pastSeries = [],
    ) {
    }

    public function fetch(): void
    {
        $series = array_map(function ($item) {
            $item['dto'] = DewPointDto::hydrateFromArray($item['payload']);

            return $item;
        }, $this->sensorRepository->getLastHoursDewpointValues());

        $this->pastSeries = $series;

        // series is ordered ASC - so grab the last one
        $current = end($series);
        $current['ts'] = TimeToHumanReadable::generate($current['ts'] ?? null);
        $this->current = $current;
    }
}
