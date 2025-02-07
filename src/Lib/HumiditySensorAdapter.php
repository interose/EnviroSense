<?php

namespace App\Lib;

use App\Repository\SensorDescriptionRepository;
use App\Repository\SensorRepository;

class HumiditySensorAdapter
{
    public function __construct(
        private readonly SensorRepository $sensorRepository,
        private readonly SensorDescriptionRepository $sensorDescriptionRepository,
    ) {
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function getCurrentData(): array
    {
        $latest = array_map(function ($item) {
            $payload = json_decode($item['payload'], true);
            $ts = \DateTime::createFromFormat('Y-m-d H:i:s', $item['ts']);

            $item['bat'] = $payload['v'] ?? 0;
            $item['ts'] = TimeToHumanReadable::generate($ts);
            $item['humidity'] = $payload['h'] ?? 0;
            $item['temperature'] = $payload['t'] ?? 0;

            return $item;
        }, $this->sensorRepository->getLatestValues());

        return array_values($latest);
    }

    /**
     * Returns the data for the last 24 hours.
     */
    public function getPastSeries(): array
    {
        $sensorHashMap = $this->sensorDescriptionRepository->getMacHashMap();
        $data = $this->sensorRepository->getLastHours();
        $hum = [];

        array_walk($data, function ($item) use (&$hum, $sensorHashMap) {
            $mac = $item['mac'];

            if (!isset($hum[$mac]['mac'])) {
                if (isset($sensorHashMap[$mac])) {
                    $hum[$mac]['mac'] = $mac;
                    $hum[$mac]['name'] = $sensorHashMap[$mac]['name'] ?? '';
                    $hum[$mac]['description'] = $sensorHashMap[$mac]['description'] ?? '';
                    $hum[$mac]['color'] = $sensorHashMap[$mac]['color'] ?? '';
                }
            }

            if (isset($sensorHashMap[$mac])) {
                $hum[$mac]['data'][] = [
                    'ts' => $item['ts']->getTimestamp(),
                    'humidity' => $item['payload']['h'] ?? 0,
                ];
            }
        });

        return $hum;
    }
}
