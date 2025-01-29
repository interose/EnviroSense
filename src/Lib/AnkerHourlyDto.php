<?php

 namespace App\Lib;

 class AnkerHourlyDto
 {
     public function __construct(
         public \DateTimeImmutable $ts,
         public string $powerUnit,
         public int $solarPower1,
         public int $solarPower2,
         public int $solarPower3,
         public int $solarPower4,
         public int $batterySoc,
         public int $batteryEnergy,
         public int $chargingPower,
     )
     {
     }

     public static function fromJson(string $json): self
     {
         $content = json_decode($json, true);

         $obj = new self(
             new \DateTimeImmutable(),
             $content['power_unit'] ?? '',
             intval($content['solar_power_1'] ?? 0),
             intval($content['solar_power_2'] ?? 0),
             intval($content['solar_power_3'] ?? 0),
             intval($content['solar_power_4'] ?? 0),
             intval($content['battery_soc'] ?? 0),
             intval($content['battery_energy'] ?? 0),
             intval($content['charging_power'] ?? 0),
         );

         return $obj;
     }
 }