<?php

namespace App\Lib;

use App\Repository\SensorRepository;

class DewpointSensorAdapter
{
    public function __construct(
        private readonly SensorRepository $sensorRepository,
        public array $current = [],
        public array $outsideDewPointSeries = [],
        public array $insideDewPointSeries = [],
    ) {
    }

    public function fetch(): void
    {
        $series = [];

        foreach ($this->sensorRepository->getLastHoursDewpointValues() as $item) {
            $dto = DewPointDto::hydrateFromArray($item['payload']);
            $timestamp = $item['ts']->getTimestamp();

            $series[] = [
                'ts' => $item['ts'],
                'outsideTemperature' => $dto->outsideTemperature,
                'outsideHumidity' => $dto->outsideHumidity,
                'outsideDewPoint' => $dto->outsideDewPoint,
                'insideTemperature' => $dto->insideTemperature,
                'insideHumidity' => $dto->insideHumidity,
                'insideDewPoint' => $dto->insideDewPoint,
                'ventilation' => $dto->ventilation,
            ];

            $this->outsideDewPointSeries[] = [
                $timestamp * 1000, $dto->outsideDewPoint
            ];

            $this->insideDewPointSeries[] = [
                $timestamp * 1000, $dto->insideDewPoint
            ];
        }

        // series is ordered ASC - so grab the last one
        $current = end($series);
        $current['ts'] = TimeToHumanReadable::generate($current['ts'] ?? null);
        $this->current = $current;
    }
}
