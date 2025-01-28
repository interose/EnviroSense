<?php

namespace App\Lib;

use App\Lib\TimeToHumanReadable;

class DashboardAdapter
{
    /**
     * taken from
     * https://www.vwew-energie.de/privatkunden/strom/classic-grundversorgung.html
     */
    private const POWER_PRICE = 30.76;

    private const ROUNDS_PER_CUBIC_METER = 0.01;
    private const GAS_PRICE = 6.45; // ct per kWh
    private const CALORIFIC_VALUE = 11.280;
    private const CONDITION_VALUE = 0.8888;

    private const DEW_POINT_SENSOR = '30:83:98:B1:60:5D';

    public function __construct(
        private \App\Repository\SensorRepository $sensorRepository,
        private \App\Repository\HeatingSystemRepository $heatingSystemRepository,
        private \App\Repository\Power\DailyRepository $powerDailyRepository,
        private \App\Repository\Power\HourlyRepository $powerHourlyRepository,
        private \App\Repository\Gas\DailyRepository $gasDailyRepository,
        private \App\Repository\Solar\DailyRepository $solarDailyRepository,
        private \App\Repository\Solar\HourlyRepository $solarHourlyRepository,
        private \App\Repository\Photovoltaics\HourlyRepository $pvHourlyRepository,
    ) {
    }

    public function getLatestDewPointSensorValue(): DewPointDto
    {
        $dewPointSensorValue = $this->sensorRepository->findOneBy(['mac' => self::DEW_POINT_SENSOR], ['ts' => 'DESC']);

        return DewPointDto::hydrateFromArray($dewPointSensorValue->getPayload());
    }

    /**
     * Gets the latest data from the self designed esp8266 sensors
     * 
     * @return array
     */
    public function getLatestHumSensorValues(): array
    {
        // filter sensor values with no name and color
        $latest = array_map(function ($item) {
            $payload = json_decode($item['payload'], true);
            $ts = \DateTime::createFromFormat('Y-m-d H:i:s', $item['ts']);

            $item['bat'] = $payload['v'] ?? 0;
            $item['ts'] = TimeToHumanReadable::generate($ts);
            $item['humidity'] = number_format($payload['h'] ?? 0, 1, ',', '.');
            $item['temperature'] = number_format($payload['t'] ?? 0, 1, ',', '.');

            return $item;
        }, $this->sensorRepository->getLatestValues());

        return array_values($latest);
    }

    /**
     * Gets the latest data from the heating system
     * 
     * @return array
     */
    public function getHeatingSystemValues(): array
    {
        return [[
            'value' => number_format((float) $this->heatingSystemRepository->getLatestValueByProperty('gettempa'), 1, ',', '.'),
            'name' => 'outside',
        ], [
            'value' => number_format((float) $this->heatingSystemRepository->getLatestValueByProperty('gettempwwist'), 1, ',', '.'),
            'name' => 'water',
        ], [
            'value' => number_format((float) $this->heatingSystemRepository->getLatestValueByProperty('gettempkoll'), 1, ',', '.'),
            'name' => 'collector',
        ]];
    }

    /**
     * Gets the latest data from the power consumption sensor
     * 
     * @return array
     */
    public function getActualPowerValues(): array
    {
        $todayConsumption = round($this->powerDailyRepository->getTodaysConsumption() / 1000, 1);
        $cost = round(($todayConsumption * self::POWER_PRICE) / 100, 1);
        $latest = $this->powerHourlyRepository->getLatestValue();

        if (abs($latest) > 1000) {
            $latestValue = number_format(round($latest / 1000, 2), 2, ',', '.');
            $latestUnit = 'kW'; 
        } else {
            $latestValue = round($latest, 0);
            $latestUnit = 'W';
        }

        return [[
            'value' => $latestValue,
            'name' => 'current',
            'unit' => $latestUnit,
        ], [
            'value' => number_format($todayConsumption, 1, ',', '.'),
            'name' => 'today',
            'unit' => 'kWh'
        ], [
            'value' => number_format($cost, 1, ',', '.'),
            'name' => 'today',
            'unit' => '€'
        ]];
    }

    /**
     * Gets the latest data from the gas consumption sensor
     * 
     * @return array
     */
    public function getActualGasValues(): array
    {
        $todayConsumption = $this->gasDailyRepository->getTodaysConsumption() * self::ROUNDS_PER_CUBIC_METER;
        $cost = round(($todayConsumption * self::CONDITION_VALUE * self::CALORIFIC_VALUE * self::GAS_PRICE) / 100, 1);

        return [[
            'value' => $todayConsumption,
            'name' => 'today',
            'unit' => '㎥'
        ], [
            'value' => number_format($cost, 1, ',', '.'),
            'name' => 'today',
            'unit' => '€'
        ]];
    }

    /**
     * Gets the latest data from the solar water sensor
     * 
     * @return array
     */
    public function getActualSolarValues(): array
    {
        $todayYield = round($this->solarDailyRepository->getTodaysYield() / 1000, 1);
        $latest = $this->solarHourlyRepository->getLatestValue();

        if (abs($latest) > 1000) {
            $latestValue = number_format(round($latest / 1000, 2), 2, ',', '.');
            $latestUnit = 'kW'; 
        } else {
            $latestValue = round($latest, 0);
            $latestUnit = 'W';
        }

        return [[
            'value' => $latestValue,
            'name' => 'current',
            'unit' => $latestUnit,
        ], [
            'value' => number_format($todayYield, 1, ',', '.'),
            'name' => 'today',
            'unit' => 'kWh'
        ]];
    }

    /**
     * Gets the latest data from the pv module
     * 
     * @return array
     */
    public function getActualPvValues(): array
    {
        $latest = $this->pvHourlyRepository->getLatestValue();

        return [[
            'value' => $latest,
            'name' => 'current',
            'unit' => 'W',
        ], [
            'value' => 0,
            'name' => 'today',
            'unit' => 'kWh'
        ]];
    }
}